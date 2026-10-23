import { useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import Icon from '../../Components/Icon';
import Layout from '../../Components/Layout';
import { Alert, Button, FieldError, TextField } from '../../Components/ui';
import { useT } from '../../lib/i18n';

const MAX_SIDE = 2000;

/**
 * Phone photos are often 4-8 MB; shrinking them before upload keeps things
 * fast on slow networks. PDFs and small images go up unchanged.
 */
async function compressImage(file) {
    if (!file.type.startsWith('image/') || file.size < 600 * 1024) return file;

    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' });
    } catch {
        return file;
    }
}

export default function Create({ types, categories, preselect, maxKb }) {
    const { t, n, pick } = useT();
    const cameraInput = useRef(null);
    const fileInput = useRef(null);
    const [compressing, setCompressing] = useState(false);
    const [preview, setPreview] = useState(null);

    const form = useForm({
        type: types[preselect] ? preselect : 'nid',
        category: '',
        title: '',
        file: null,
    });

    const pickFile = async (e) => {
        const chosen = e.target.files?.[0];
        if (!chosen) return;
        setCompressing(true);
        const file = await compressImage(chosen);
        setCompressing(false);
        form.setData('file', file);
        setPreview(file.type.startsWith('image/') ? URL.createObjectURL(file) : null);
    };

    const submit = (e) => {
        e.preventDefault();
        form.post('/documents', { forceFormData: true });
    };

    return (
        <Layout title={t('uploadTitle')}>
            <h1 className="mb-5 text-2xl font-semibold">{t('uploadTitle')}</h1>

            <form onSubmit={submit} className="space-y-6">
                <fieldset>
                    <legend className="mb-2 font-medium">{t('whichPaper')}</legend>
                    <div className="grid grid-cols-2 gap-2">
                        {Object.entries(types).map(([key, type]) => (
                            <label
                                key={key}
                                className={`flex min-h-14 cursor-pointer items-center gap-2 rounded-xl border p-3 has-focus-visible:ring-2 has-focus-visible:ring-brand ${
                                    form.data.type === key ? 'border-brand bg-brand-soft font-semibold' : 'border-line bg-card'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="type"
                                    value={key}
                                    checked={form.data.type === key}
                                    onChange={() => form.setData('type', key)}
                                    className="sr-only"
                                />
                                {form.data.type === key && <Icon name="check" className="size-5 shrink-0 text-brand" />}
                                <span className="leading-snug">{pick(type)}</span>
                            </label>
                        ))}
                    </div>
                    <FieldError message={form.errors.type} />
                </fieldset>

                {form.data.type === 'other' && (
                    <div>
                        <label htmlFor="category" className="mb-1.5 block font-medium">
                            {t('folder')}
                        </label>
                        <select
                            id="category"
                            value={form.data.category}
                            onChange={(e) => form.setData('category', e.target.value)}
                            className="block w-full rounded-xl border border-line bg-white px-4 py-3 text-lg"
                            required
                        >
                            <option value="" />
                            {Object.entries(categories).map(([key, label]) => (
                                <option key={key} value={key}>
                                    {pick(label)}
                                </option>
                            ))}
                        </select>
                        <FieldError message={form.errors.category} />
                    </div>
                )}

                <TextField
                    id="title"
                    label={t('titleOptional')}
                    placeholder={t('titlePlaceholder')}
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    error={form.errors.title}
                    maxLength={100}
                />

                <div>
                    <div className="grid grid-cols-2 gap-2">
                        <Button type="button" variant="secondary" onClick={() => cameraInput.current.click()}>
                            <Icon name="camera" />
                            {t('takePhoto')}
                        </Button>
                        <Button type="button" variant="secondary" onClick={() => fileInput.current.click()}>
                            <Icon name="image" />
                            {t('chooseFile')}
                        </Button>
                    </div>
                    <input ref={cameraInput} type="file" accept="image/*" capture="environment" className="hidden" onChange={pickFile} />
                    <input ref={fileInput} type="file" accept="image/*,application/pdf" className="hidden" onChange={pickFile} />
                    <p className="mt-2 text-sm text-muted">{t('fileRules', { mb: n(Math.round(maxKb / 1024)) })}</p>
                    {compressing && <p className="mt-2 text-sm text-muted">{t('compressing')}</p>}
                    {form.data.file && !compressing && (
                        <div className="mt-3 flex items-center gap-3 rounded-xl border border-line bg-card p-3">
                            {preview ? (
                                <img src={preview} alt="" className="size-16 rounded-lg object-cover" />
                            ) : (
                                <Icon name="papers" className="size-10 text-brand" />
                            )}
                            <span className="min-w-0 truncate text-sm">{form.data.file.name}</span>
                        </div>
                    )}
                    <FieldError message={form.errors.file} />
                </div>

                <Alert tone="info">{t('encryptedNote')}</Alert>

                <Button type="submit" className="w-full" disabled={!form.data.file || compressing || form.processing}>
                    {form.processing ? t('saving') : t('save')}
                </Button>
            </form>
        </Layout>
    );
}
