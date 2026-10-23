import { usePage } from '@inertiajs/react';

const strings = {
    appName: { bn: 'ফর্মসাথী', en: 'FormSathi' },
    notGovt: {
        bn: 'ফর্মসাথী কোনো সরকারি সেবা নয়। আমরা শুধু তথ্য সাজিয়ে দিই — আবেদন আপনি নিজে জমা দেন।',
        en: 'FormSathi is not a government service. We lay out your details — you submit the application yourself.',
    },
    helpline: { bn: 'হেল্পলাইন', en: 'Helpline' },
    switchLang: { bn: 'English', en: 'বাংলা' },

    // Navigation
    navHome: { bn: 'হোম', en: 'Home' },
    navPapers: { bn: 'কাগজপত্র', en: 'Papers' },
    navForms: { bn: 'ফর্ম', en: 'Forms' },
    navProfile: { bn: 'প্রোফাইল', en: 'Profile' },
    navSettings: { bn: 'সেটিংস', en: 'Settings' },
    lockNow: { bn: 'লক করুন', en: 'Lock' },

    // Sign-in
    welcome: { bn: 'স্বাগতম', en: 'Welcome' },
    mobileIntro: {
        bn: 'আপনার কাগজ একবার রাখুন, যেকোনো ফর্মের তথ্য মিনিটে সাজিয়ে নিন।',
        en: 'Keep your papers once, get any form’s details laid out in minutes.',
    },
    mobileLabel: { bn: 'মোবাইল নম্বর', en: 'Mobile number' },
    mobileHint: { bn: 'এই নম্বরে একটি কোড যাবে', en: 'We’ll send a code to this number' },
    sendCode: { bn: 'কোড পাঠান', en: 'Send code' },
    codeTitle: { bn: 'কোডটি লিখুন', en: 'Enter the code' },
    codeSentTo: { bn: ':mobile নম্বরে ৬ সংখ্যার কোড পাঠানো হয়েছে।', en: 'We sent a 6-digit code to :mobile.' },
    codeLabel: { bn: 'এসএমএস কোড', en: 'SMS code' },
    verify: { bn: 'যাচাই করুন', en: 'Verify' },
    resend: { bn: 'আবার কোড পাঠান', en: 'Send the code again' },
    changeNumber: { bn: 'নম্বর বদলান', en: 'Change number' },
    devOtp: { bn: 'শুধু ডেভেলপমেন্টে: কোড :code', en: 'Development only: code :code' },
    pinSetTitle: { bn: '৬ সংখ্যার পিন বানান', en: 'Create a 6-digit PIN' },
    pinSetHint: {
        bn: 'কাগজ দেখতে ও অ্যাপ খুলতে এই পিন লাগবে। কাউকে বলবেন না।',
        en: 'You’ll need this PIN to open the app and view papers. Don’t share it.',
    },
    pinEnterTitle: { bn: 'আপনার পিন দিন', en: 'Enter your PIN' },
    pinLabel: { bn: 'পিন', en: 'PIN' },
    pinAgain: { bn: 'পিন আবার দিন', en: 'PIN again' },
    continue: { bn: 'এগিয়ে যান', en: 'Continue' },
    lockedTitle: { bn: 'অ্যাপ লক করা আছে', en: 'The app is locked' },
    lockedHint: {
        bn: 'কিছুক্ষণ ব্যবহার না হওয়ায় নিরাপত্তার জন্য লক হয়েছে।',
        en: 'It locked itself after a few idle minutes, for your safety.',
    },
    unlock: { bn: 'খুলুন', en: 'Unlock' },
    logout: { bn: 'লগআউট', en: 'Sign out' },

    // Dashboard
    hello: { bn: 'আপনার কাজের জায়গা', en: 'Your space' },
    profileReady: { bn: 'প্রোফাইল তৈরি', en: 'Profile ready' },
    papersStored: { bn: 'সংরক্ষিত কাগজ', en: 'Papers stored' },
    nextStep: { bn: 'পরের ধাপ', en: 'Next step' },
    startWithNid: { bn: 'জাতীয় পরিচয়পত্রের ছবি তুলে শুরু করুন', en: 'Start by photographing your NID' },
    startWithNidHint: {
        bn: 'এনআইডি থেকে নাম, জন্ম তারিখ, বাবা-মায়ের নাম পাওয়া যায়।',
        en: 'Your NID gives your name, date of birth and parents’ names.',
    },
    reviewMismatches: { bn: ':count টি তথ্য কাগজে কাগজে আলাদা — দেখে নিন', en: ':count details differ between papers — review them' },
    fillProfile: { bn: 'প্রোফাইলের বাকি তথ্য দিন', en: 'Fill in the rest of your profile' },
    addPaper: { bn: 'কাগজ যোগ করুন', en: 'Add a paper' },
    expiryTitle: { bn: 'মেয়াদ শেষ হচ্ছে', en: 'Expiring soon' },
    expiresOn: { bn: 'মেয়াদ শেষ :date', en: 'Expires :date' },
    expiredOn: { bn: 'মেয়াদ শেষ হয়েছে :date', en: 'Expired :date' },
    fillAForm: { bn: 'ফর্ম পূরণ করুন', en: 'Fill a form' },
    fillAFormHint: {
        bn: 'ই-পাসপোর্ট আবেদন — প্রোফাইলের তথ্য ফর্মের ক্রমে, এক ট্যাপে কপি।',
        en: 'E-passport application — your profile in the form’s order, one tap to copy.',
    },
    continueApplication: { bn: 'চালিয়ে যান: :name', en: 'Continue: :name' },

    // Forms and copy-assist
    formsTitle: { bn: 'ফর্ম পূরণ', en: 'Fill a form' },
    formsIntro: {
        bn: 'আপনার প্রোফাইলের তথ্য ফর্মের ক্রমে সাজানো হবে। অফিসিয়াল সাইটে কপি-পেস্ট করে আবেদন আপনি নিজে জমা দেবেন।',
        en: 'Your profile is laid out in the form’s order. You copy each field into the official site and submit it yourself.',
    },
    startForm: { bn: 'শুরু করুন', en: 'Start' },
    resumeForm: { bn: 'চালিয়ে যান', en: 'Continue' },
    myApplications: { bn: 'আমার আবেদন', en: 'My applications' },
    statusDraft: { bn: 'চলছে · শুরু :date', en: 'In progress · started :date' },
    statusCompleted: { bn: 'জমা দেওয়া হয়েছে :date', en: 'Submitted :date' },
    checkTitle: { bn: 'শুরুর আগে দেখে নিন', en: 'Before you start' },
    missingRequired: { bn: ':count টি জরুরি তথ্য প্রোফাইলে নেই', en: ':count required details are missing from your profile' },
    reviewNeeded: { bn: ':count টি তথ্য কাগজে কাগজে আলাদা', en: ':count details differ between your papers' },
    allReady: {
        bn: 'সব জরুরি তথ্য প্রস্তুত। অফিসিয়াল সাইট খুলে একে একে কপি করুন।',
        en: 'All required details are ready. Open the official site and copy each field in turn.',
    },
    openOfficial: { bn: 'অফিসিয়াল সাইট খুলুন', en: 'Open the official site' },
    fixInProfile: { bn: 'প্রোফাইলে ঠিক করুন', en: 'Fix in profile' },
    progressCopied: { bn: ':done/:total কপি হয়েছে', en: ':done of :total copied' },
    onSite: { bn: 'সাইটে লেখা', en: 'On the site' },
    copy: { bn: 'কপি', en: 'Copy' },
    copiedTick: { bn: 'কপি হয়েছে', en: 'Copied' },
    copyFailed: { bn: 'কপি হয়নি — লেখাটি বেছে নিজে কপি করুন', en: 'Couldn’t copy — select the text and copy it by hand' },
    notInProfile: { bn: 'প্রোফাইলে নেই', en: 'Not in your profile' },
    addInProfile: { bn: 'প্রোফাইলে যোগ করুন', en: 'Add to profile' },
    required: { bn: 'জরুরি', en: 'Required' },
    optional: { bn: 'ঐচ্ছিক', en: 'Optional' },
    differsWarning: { bn: 'কাগজে কাগজে আলাদা — আগে প্রোফাইলে ঠিক করুন', en: 'Your papers disagree — fix it in your profile first' },
    printSummary: { bn: 'প্রিন্টযোগ্য সারাংশ', en: 'Printable summary' },
    print: { bn: 'প্রিন্ট করুন', en: 'Print' },
    markSubmitted: { bn: 'অফিসিয়াল সাইটে জমা দিয়েছি', en: 'I’ve submitted on the official site' },
    markSubmittedConfirm: {
        bn: 'অফিসিয়াল সাইটে আবেদন জমা দিয়েছেন? এখনকার তথ্যগুলো এই রেকর্ডে সংরক্ষিত থাকবে।',
        en: 'Have you submitted on the official site? The current details will be kept with this record.',
    },
    submittedNote: {
        bn: 'অফিসিয়াল সাইটের আবেদন সারাংশ প্রিন্ট করে পাসপোর্ট অফিসে সাথে নিয়ে যান।',
        en: 'Print the official site’s application summary and take it to the passport office.',
    },
    resetTicks: { bn: 'টিক মুছে আবার শুরু', en: 'Clear ticks and start over' },
    deleteApplication: { bn: 'রেকর্ড মুছুন', en: 'Delete record' },
    deleteApplicationConfirm: { bn: 'এই আবেদনের রেকর্ড মুছে যাবে। নিশ্চিত?', en: 'This application record will be deleted. Sure?' },
    unverifiedTemplate: {
        bn: 'এই ফর্মের ক্রম এখনো অফিসিয়াল সাইটের সাথে মিলিয়ে দেখা হয়নি। পেস্ট করার আগে প্রতিটি ঘরের নাম মিলিয়ে নিন।',
        en: 'This form’s order hasn’t been checked against the official site yet. Match each field’s label before pasting.',
    },
    summaryFooter: { bn: 'ফর্মসাথী থেকে তৈরি নিজস্ব সারাংশ — সরকারি নথি নয়।', en: 'Your own summary made with FormSathi — not a government document.' },
    printedOn: { bn: 'তৈরি :date', en: 'Made :date' },

    // Documents
    papersTitle: { bn: 'আমার কাগজপত্র', en: 'My papers' },
    noPapers: { bn: 'এখনো কোনো কাগজ নেই।', en: 'No papers yet.' },
    detailsFilled: { bn: ':done/:total তথ্য লেখা', en: ':done/:total details entered' },
    needsDetails: { bn: 'তথ্য লেখা বাকি', en: 'Details not entered' },
    uploadTitle: { bn: 'নতুন কাগজ', en: 'New paper' },
    whichPaper: { bn: 'কোন কাগজ?', en: 'Which paper?' },
    folder: { bn: 'ফোল্ডার', en: 'Folder' },
    titleOptional: { bn: 'নাম (ঐচ্ছিক)', en: 'Name (optional)' },
    titlePlaceholder: { bn: 'যেমন: বাবার এনআইডি', en: 'e.g. Father’s NID' },
    takePhoto: { bn: 'ছবি তুলুন', en: 'Take a photo' },
    chooseFile: { bn: 'গ্যালারি বা PDF', en: 'Gallery or PDF' },
    fileRules: { bn: 'ছবি বা PDF, সর্বোচ্চ :mb MB', en: 'Photo or PDF, up to :mb MB' },
    compressing: { bn: 'ছবি ছোট করা হচ্ছে…', en: 'Shrinking the photo…' },
    save: { bn: 'সংরক্ষণ করুন', en: 'Save' },
    saving: { bn: 'সংরক্ষণ হচ্ছে…', en: 'Saving…' },
    encryptedNote: {
        bn: 'আপনার কাগজ আপনার নিজস্ব চাবি দিয়ে এনক্রিপ্ট করে রাখা হয়। আমাদের কর্মীরাও দেখতে পারেন না।',
        en: 'Your papers are encrypted with your own key. Our staff can’t see them either.',
    },
    viewPaper: { bn: 'কাগজ দেখুন', en: 'View paper' },
    pinToView: { bn: 'কাগজ দেখতে পিন দিন', en: 'Enter your PIN to view the paper' },
    openFull: { bn: 'পুরো আকারে খুলুন', en: 'Open full size' },
    detailsOnPaper: { bn: 'কাগজে যা লেখা আছে', en: 'What the paper says' },
    detailsOnPaperHint: {
        bn: 'কাগজে যেমন বানান, ঠিক তেমন লিখুন — নিজে থেকে বদলাবেন না। শীঘ্রই এগুলো স্বয়ংক্রিয়ভাবে পড়া হবে।',
        en: 'Type each detail exactly as spelled on the paper — don’t correct it. Soon these will be read automatically.',
    },
    saveDetails: { bn: 'তথ্য সংরক্ষণ করুন', en: 'Save details' },
    noFieldsForType: { bn: 'এই কাগজ থেকে প্রোফাইলের তথ্য নেওয়া হয় না।', en: 'This paper doesn’t feed the profile.' },
    deletePaper: { bn: 'কাগজ মুছে ফেলুন', en: 'Delete paper' },
    deleteConfirm: {
        bn: 'এই কাগজ ও এর তথ্য স্থায়ীভাবে মুছে যাবে। নিশ্চিত?',
        en: 'This paper and its details will be deleted permanently. Sure?',
    },

    // Profile
    profileTitle: { bn: 'মাস্টার প্রোফাইল', en: 'Master profile' },
    profileIntro: {
        bn: 'একবার ঠিক করুন, প্রতিটি ফর্মে এখান থেকেই তথ্য যাবে।',
        en: 'Get it right once; every form takes its details from here.',
    },
    mismatchTitle: { bn: 'কাগজে কাগজে মিল নেই', en: 'Papers disagree' },
    mismatchHint: {
        bn: 'আবেদনের আগে ঠিক করুন — অমিল থাকলে আবেদন আটকে যেতে পারে। কোনটি সঠিক বেছে নিন।',
        en: 'Fix this before applying — a mismatch can block an application. Pick the correct one.',
    },
    resolvedHint: {
        bn: 'আপনি একটি বেছে নিয়েছেন। তবে কাগজগুলোতে এখনো আলাদা লেখা — প্রয়োজনে কাগজ সংশোধন করুন।',
        en: 'You picked one. The papers still differ — get a paper corrected if needed.',
    },
    useThis: { bn: 'এটি সঠিক', en: 'This is correct' },
    chosen: { bn: 'বেছে নেওয়া', en: 'Chosen' },
    onPapers: { bn: 'যেসব কাগজে আছে', en: 'On these papers' },
    fromPaper: { bn: 'উৎস: :paper', en: 'From: :paper' },
    typedByYou: { bn: 'আপনার লেখা', en: 'Typed by you' },
    notConfirmed: { bn: 'যাচাই বাকি', en: 'Not confirmed' },
    confirmed: { bn: 'যাচাই করা', en: 'Confirmed' },
    confirm: { bn: 'ঠিক আছে', en: 'Confirm' },
    edit: { bn: 'বদলান', en: 'Edit' },
    add: { bn: 'যোগ করুন', en: 'Add' },
    cancel: { bn: 'বাতিল', en: 'Cancel' },
    empty: { bn: 'খালি', en: 'Empty' },
    show: { bn: 'দেখান', en: 'Show' },
    hide: { bn: 'লুকান', en: 'Hide' },
    sectionNames: { bn: 'নাম ও পরিবার', en: 'Name and family' },
    sectionPersonal: { bn: 'ব্যক্তিগত তথ্য', en: 'Personal details' },
    sectionIds: { bn: 'পরিচয় নম্বর', en: 'ID numbers' },
    sectionPresent: { bn: 'বর্তমান ঠিকানা', en: 'Present address' },
    sectionPermanent: { bn: 'স্থায়ী ঠিকানা', en: 'Permanent address' },
    sectionContact: { bn: 'যোগাযোগ', en: 'Contact' },
    choose: { bn: 'বেছে নিন', en: 'Choose' },

    // Settings
    settingsTitle: { bn: 'সেটিংস', en: 'Settings' },
    language: { bn: 'ভাষা', en: 'Language' },
    account: { bn: 'অ্যাকাউন্ট', en: 'Account' },
    deleteAll: { bn: 'আমার সব তথ্য মুছে ফেলুন', en: 'Delete all my data' },
    deleteAllHint: {
        bn: 'সব কাগজ, প্রোফাইল ও অ্যাকাউন্ট স্থায়ীভাবে মুছে যাবে। ব্যাকআপ থেকেও ৩০ দিনের মধ্যে মুছে যাবে। এটি ফেরানো যায় না।',
        en: 'All papers, your profile and your account are deleted permanently, and from backups within 30 days. This can’t be undone.',
    },
    deleteAllConfirm: { bn: 'পিন দিয়ে সব মুছুন', en: 'Delete everything with PIN' },
};

const bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

export function toBnDigits(value) {
    return String(value).replace(/\d/g, (d) => bnDigits[d]);
}

export function useLocale() {
    return usePage().props.locale ?? 'bn';
}

/**
 * t('key', {param}) returns the string in the current language, with
 * digits in Bangla when the interface is Bangla. n() and date() format
 * numbers and dates the same way.
 */
export function useT() {
    const locale = useLocale();
    const n = (value) => (locale === 'bn' ? toBnDigits(value) : String(value));

    const t = (key, params = {}) => {
        let text = strings[key]?.[locale] ?? key;
        for (const [name, value] of Object.entries(params)) {
            text = text.replace(`:${name}`, typeof value === 'number' ? n(value) : value);
        }
        return text;
    };

    const date = (iso) => {
        if (!iso) return '';
        const [y, m, d] = iso.split('-');
        return n(`${d}/${m}/${y}`);
    };

    // Picks the right half of a {bn, en} label from the server.
    const pick = (label) => label?.[locale] ?? '';

    return { t, n, date, pick, locale };
}
