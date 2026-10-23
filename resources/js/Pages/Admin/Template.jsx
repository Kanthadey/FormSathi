import { router, useForm, usePage } from '@inertiajs/react';
import { Alert, Button, Card, TextField } from '../../Components/ui';
import AdminShell from './AdminShell';

export default function Template({ template, formats, profileKeys }) {
    const { errors } = usePage().props;
    const form = useForm({
        nameBn: template.nameBn,
        nameEn: template.nameEn,
        officialUrl: template.officialUrl ?? '',
        sections: template.sections,
    });
    const sectionErrors = Object.entries(errors)
        .filter(([key]) => key.startsWith('sections'))
        .map(([, message]) => message);

    return (
        <AdminShell title={`${template.nameEn} · v${template.version}`}>
            <Card className="mb-5 flex flex-wrap items-center gap-3">
                <span className={template.active ? 'font-medium text-ok' : 'text-muted'}>{template.active ? 'Live' : 'Inactive'}</span>
                <span className={template.verifiedAt ? 'text-ok' : 'text-warn'}>
                    {template.verifiedAt ? `Checked against the official form ${template.verifiedAt}` : 'Not checked against the official form'}
                </span>
                <span className="flex-1" />
                {!template.active && (
                    <Button
                        className="min-h-10 py-1.5 text-sm"
                        onClick={() => window.confirm('Make this version the one all users get?') && router.post(`/admin/templates/${template.id}/publish`)}
                    >
                        Publish this version
                    </Button>
                )}
                {!template.verifiedAt && (
                    <Button
                        variant="secondary"
                        className="min-h-10 py-1.5 text-sm"
                        onClick={() =>
                            window.confirm('Did you go through the live official form and confirm every field, label and the order match?') &&
                            router.post(`/admin/templates/${template.id}/verify`)
                        }
                    >
                        Mark as checked
                    </Button>
                )}
            </Card>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(`/admin/templates/${template.id}`, { preserveScroll: true });
                }}
                className="space-y-4"
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <TextField id="nameBn" label="Name (Bangla)" value={form.data.nameBn} onChange={(e) => form.setData('nameBn', e.target.value)} error={errors.nameBn} />
                    <TextField id="nameEn" label="Name (English)" value={form.data.nameEn} onChange={(e) => form.setData('nameEn', e.target.value)} error={errors.nameEn} />
                </div>
                <TextField
                    id="officialUrl"
                    label="Official site URL"
                    value={form.data.officialUrl}
                    onChange={(e) => form.setData('officialUrl', e.target.value)}
                    error={errors.officialUrl}
                />
                {sectionErrors.length > 0 && (
                    <Alert tone="danger">
                        <ul className="list-disc pl-5">
                            {sectionErrors.map((m, i) => (
                                <li key={i}>{m}</li>
                            ))}
                        </ul>
                    </Alert>
                )}
                <TextField
                    id="sections"
                    as="textarea"
                    rows={24}
                    label="Sections and fields (JSON, in the form's order)"
                    hint={`Formats: ${formats.join(', ')}. Profile keys: ${profileKeys.join(', ')}.`}
                    value={form.data.sections}
                    onChange={(e) => form.setData('sections', e.target.value)}
                    spellCheck={false}
                    className="[&_textarea]:font-mono [&_textarea]:text-xs"
                />
                <Button type="submit" disabled={form.processing || !form.isDirty}>
                    Save as new version
                </Button>
                <p className="text-sm text-muted">Saving never changes this version. It creates the next version, inactive until you publish it.</p>
            </form>
        </AdminShell>
    );
}
