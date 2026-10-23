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
        bn: 'কাগজে যেমন বানান, ঠিক তেমন লিখুন — নিজে থেকে বদলাবেন না।',
        en: 'Type each detail exactly as spelled on the paper — don’t correct it.',
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

    // Assistant (F7)
    assistantTitle: { bn: 'কী করতে চান?', en: 'What do you want to do?' },
    assistantHint: { bn: 'যেমন: “আমি পাসপোর্ট করতে চাই” বা “চাকরির জন্য সিভি লাগবে”', en: 'e.g. “I want to apply for a passport” or “I need a CV for a job”' },
    assistantPlaceholder: { bn: 'বাংলা, ইংরেজি বা বাংলিশে লিখুন…', en: 'Type in Bangla, English or Banglish…' },
    ask: { bn: 'জিজ্ঞেস করুন', en: 'Ask' },
    thinking: { bn: 'বুঝে নিচ্ছি…', en: 'Working it out…' },
    open: { bn: 'খুলুন', en: 'Open' },
    newConversation: { bn: 'নতুন করে শুরু', en: 'Start over' },
    you: { bn: 'আপনি', en: 'You' },

    // Plans and payment (F11)
    navPlans: { bn: 'প্ল্যান', en: 'Plan' },
    plansTitle: { bn: 'প্ল্যান ও পেমেন্ট', en: 'Plan and payment' },
    currentPlan: { bn: 'আপনার প্ল্যান', en: 'Your plan' },
    planFree: { bn: 'ফ্রি', en: 'Free' },
    planPremium: { bn: 'প্রিমিয়াম', en: 'Premium' },
    premiumUntil: { bn: 'মেয়াদ :date পর্যন্ত', en: 'Until :date' },
    freeLeft: { bn: 'এ মাসে আর :left/:total টি ফ্রি ফর্ম সহায়তা', en: ':left of :total free form assists left this month' },
    creditsLeft: { bn: ':count টি ফর্ম প্যাক বাকি', en: ':count form packs left' },
    papersUsed: { bn: ':used/:limit কাগজ রাখা হয়েছে', en: ':used of :limit papers stored' },
    payWithBkash: { bn: 'বিকাশে পরিশোধ', en: 'Pay with bKash' },
    perApplication: { bn: 'প্রতি আবেদন', en: 'per application' },
    perMonth: { bn: 'প্রতি মাস', en: 'per month' },
    perYear: { bn: 'প্রতি বছর', en: 'per year' },
    featFormPack: { bn: 'একটি আবেদনে পূর্ণ সহায়তা, অমিল যাচাই, প্রিন্টযোগ্য সারাংশ', en: 'Full help with one application, mismatch check, printable summary' },
    featPremium: { bn: 'সীমাহীন ফর্ম, সীমাহীন এআই সিভি ও কভার লেটার, ১০০টি কাগজ', en: 'Unlimited forms, unlimited AI CVs and cover letters, 100 papers' },
    featFree: { bn: '১০টি কাগজ, মাস্টার প্রোফাইল, মাসে ৩টি ফর্ম সহায়তা', en: '10 papers, master profile, 3 form assists a month' },
    paymentHistory: { bn: 'পেমেন্টের ইতিহাস', en: 'Payment history' },
    payStatus_completed: { bn: 'সফল', en: 'Paid' },
    payStatus_initiated: { bn: 'অসম্পূর্ণ', en: 'Not finished' },
    payStatus_failed: { bn: 'ব্যর্থ', en: 'Failed' },
    payStatus_cancelled: { bn: 'বাতিল', en: 'Cancelled' },
    getMore: { bn: 'আরও ফর্ম সহায়তা নিন', en: 'Get more form assists' },
    fakeCheckoutTitle: { bn: 'পরীক্ষামূলক পেমেন্ট', en: 'Test payment' },
    fakeCheckoutHint: {
        bn: 'এটি শুধু ডেভেলপমেন্টের জন্য। আসল সাইটে এখানে বিকাশের পেমেন্ট পাতা খুলবে।',
        en: 'Development only. On the live site, bKash’s own payment page opens here.',
    },
    fakePay: { bn: 'পরিশোধ হয়েছে ধরে নিন', en: 'Simulate a successful payment' },
    fakeCancel: { bn: 'বাতিল করুন', en: 'Cancel' },

    // Career record and job kit (F10)
    careerTitle: { bn: 'শিক্ষা ও কাজের অভিজ্ঞতা', en: 'Education and work' },
    careerIntro: {
        bn: 'সিভি শুধু এখানকার তথ্য দিয়েই তৈরি হয় — নিজে থেকে কিছু যোগ করা হয় না। তাই সব সঠিকভাবে লিখুন।',
        en: 'Your CV is built only from what you enter here — nothing is made up. So keep it accurate.',
    },
    addItem: { bn: 'যোগ করুন', en: 'Add' },
    noItems: { bn: 'এখনো কিছু নেই।', en: 'Nothing yet.' },
    delete: { bn: 'মুছুন', en: 'Delete' },
    jobsTitle: { bn: 'চাকরির আবেদন কিট', en: 'Job application kit' },
    jobsIntro: {
        bn: 'চাকরির বিজ্ঞাপন পেস্ট করুন — আপনার তথ্য থেকে সেই চাকরির জন্য সিভি, কভার লেটার ও প্রশ্নের উত্তর তৈরি হবে।',
        en: 'Paste a job advert — get a CV, cover letter and answers for that job, built from your own details.',
    },
    newJobKit: { bn: 'নতুন কিট', en: 'New kit' },
    noKits: { bn: 'এখনো কোনো কিট নেই।', en: 'No kits yet.' },
    advertLabel: { bn: 'চাকরির বিজ্ঞাপন', en: 'Job advert' },
    advertHint: { bn: 'বিজ্ঞাপনের পুরো লেখা এখানে পেস্ট করুন (পদ, যোগ্যতা, প্রশ্ন সহ)।', en: 'Paste the whole advert here (title, requirements and any questions).' },
    generate: { bn: 'কিট তৈরি করুন', en: 'Build the kit' },
    generating: { bn: 'তৈরি হচ্ছে… এক মিনিট পর্যন্ত লাগতে পারে', en: 'Building… this can take up to a minute' },
    careerMissing: { bn: 'আগে আপনার শিক্ষা, কাজ ও দক্ষতা যোগ করুন — না হলে সিভি দুর্বল হবে।', en: 'Add your education, work and skills first — otherwise the CV will be thin.' },
    careerCounts: { bn: 'শিক্ষা :education · কাজ :work · দক্ষতা :skill', en: 'Education :education · Work :work · Skills :skill' },
    editCareer: { bn: 'শিক্ষা ও কাজ ঠিক করুন', en: 'Edit education and work' },
    aiOff: { bn: 'এআই সেবা এখনো চালু হয়নি (API key নেই)। কিট তৈরি করা যাবে না।', en: 'The AI service isn’t set up yet (no API key), so kits can’t be built.' },
    tabCv: { bn: 'সিভি', en: 'CV' },
    tabLetter: { bn: 'কভার লেটার', en: 'Cover letter' },
    tabAnswers: { bn: 'প্রশ্নের উত্তর', en: 'Answers' },
    tabAdvert: { bn: 'বিজ্ঞাপন', en: 'Advert' },
    gapsTitle: { bn: 'বিজ্ঞাপনে চাওয়া কিন্তু আপনার তথ্যে নেই', en: 'Asked for in the advert but not in your record' },
    gapsHint: { bn: 'এগুলো সিভিতে বানিয়ে দেওয়া হয়নি। সত্যিই থাকলে আপনার তথ্যে যোগ করে নতুন কিট বানান।', en: 'These were not made up in your CV. If you do have them, add them to your record and build a new kit.' },
    downloadPdf: { bn: 'PDF ডাউনলোড', en: 'Download PDF' },
    summaryLabel: { bn: 'সারসংক্ষেপ (সিভির শুরুতে)', en: 'Summary (top of the CV)' },
    keySkills: { bn: 'মূল দক্ষতা', en: 'Key skills' },
    experience: { bn: 'অভিজ্ঞতা', en: 'Experience' },
    education: { bn: 'শিক্ষা', en: 'Education' },
    noAnswers: { bn: 'বিজ্ঞাপনে কোনো প্রশ্ন পাওয়া যায়নি।', en: 'The advert asks no questions.' },
    reviewEdit: { bn: 'পড়ে দেখুন, প্রয়োজনে বদলান, তারপর সংরক্ষণ করুন।', en: 'Read it, change anything you need, then save.' },
    saveChanges: { bn: 'পরিবর্তন সংরক্ষণ', en: 'Save changes' },

    // OCR (F4)
    ocrPending: { bn: 'কাগজটি স্বয়ংক্রিয়ভাবে পড়া হচ্ছে…', en: 'Reading the paper automatically…' },
    ocrRead: { bn: 'কাগজ থেকে পড়া তথ্য বসানো হয়েছে। প্রতিটি মিলিয়ে দেখে তারপর সংরক্ষণ করুন।', en: 'Details read from the paper are filled in. Check each one, then save.' },
    ocrNothing: { bn: 'ছবি থেকে কিছু পড়া যায়নি। নিজে লিখুন, অথবা আরও পরিষ্কার ছবি দিন।', en: 'Nothing could be read from the photo. Type the details, or upload a clearer photo.' },
    ocrFailed: { bn: 'স্বয়ংক্রিয়ভাবে পড়া যায়নি। নিজে লিখুন।', en: 'Automatic reading failed. Please type the details.' },
    ocrSuggested: { bn: 'স্বয়ংক্রিয়ভাবে পড়া — মিলিয়ে নিন', en: 'Read automatically — please check' },

    // Help and tickets (F12)
    navHelp: { bn: 'সাহায্য', en: 'Help' },
    helpTitle: { bn: 'সাহায্য ও অভিযোগ', en: 'Help and support' },
    helpIntro: { bn: 'সমস্যা হলে লিখুন — আমাদের দল উত্তর দেবে। আমাদের কর্মীরা আপনার কাগজ দেখতে পারেন না।', en: 'Tell us about any problem — our team will reply. Our staff can’t see your papers.' },
    subject: { bn: 'বিষয়', en: 'Subject' },
    message: { bn: 'বার্তা', en: 'Message' },
    send: { bn: 'পাঠান', en: 'Send' },
    myTickets: { bn: 'আমার বার্তা', en: 'My messages' },
    ticket_open: { bn: 'উত্তরের অপেক্ষায়', en: 'Waiting for reply' },
    ticket_answered: { bn: 'উত্তর এসেছে', en: 'Answered' },
    ticket_closed: { bn: 'বন্ধ', en: 'Closed' },
    staffReply: { bn: 'ফর্মসাথী দল', en: 'FormSathi team' },
    reply: { bn: 'উত্তর দিন', en: 'Reply' },
    reportWrong: { bn: 'ঘরটি ভুল মনে হচ্ছে?', en: 'Field looks wrong?' },
    reportWrongPrompt: { bn: 'অফিসিয়াল সাইটে কী আলাদা দেখছেন? (ঐচ্ছিক)', en: 'What looks different on the official site? (optional)' },

    // Public pages
    login: { bn: 'লগইন / নিবন্ধন', en: 'Log in / Sign up' },
    getStarted: { bn: 'বিনামূল্যে শুরু করুন', en: 'Start free' },
    heroTitle: { bn: 'কাগজ একবার রাখুন, যেকোনো ফর্ম মিনিটে', en: 'Keep your papers once. Any form in minutes.' },
    heroText: {
        bn: 'পাসপোর্ট, ভিসা বা চাকরির ফর্মে বারবার একই তথ্য লিখবেন না। ফর্মসাথী আপনার কাগজ থেকে তথ্য সাজিয়ে দেয় — বানান মিলিয়ে, ফর্মের ক্রমে।',
        en: 'Stop retyping the same details for passport, visa and job forms. FormSathi lays out your details from your papers — spellings checked, in the form’s order.',
    },
    howTitle: { bn: 'কীভাবে কাজ করে', en: 'How it works' },
    how1: { bn: 'কাগজের ছবি তুলুন', en: 'Photograph your papers' },
    how1Text: { bn: 'এনআইডি, জন্ম নিবন্ধন, সনদ, পুরনো পাসপোর্ট — এনক্রিপ্ট করে নিরাপদে রাখা হয়।', en: 'NID, birth registration, certificates, old passport — stored encrypted.' },
    how2: { bn: 'অমিল আগেই ধরা পড়ে', en: 'Mismatches caught early' },
    how2Text: { bn: 'এনআইডিতে “Mohammad”, সনদে “Md.”? জমা দেওয়ার আগেই জানতে পারবেন।', en: '“Mohammad” on the NID, “Md.” on the certificate? You’ll know before you apply.' },
    how3: { bn: 'এক ট্যাপে কপি', en: 'Copy in one tap' },
    how3Text: { bn: 'ফর্মের ক্রমে প্রতিটি তথ্য — কপি করে অফিসিয়াল সাইটে পেস্ট করুন। জমা দেন আপনি নিজে।', en: 'Every detail in the form’s order — copy and paste into the official site. You submit it yourself.' },
    whatTitle: { bn: 'যা যা পাবেন', en: 'What you get' },
    trustTitle: { bn: 'আপনার তথ্য, আপনার নিয়ন্ত্রণে', en: 'Your data, under your control' },
    trust1: { bn: 'প্রতিটি ব্যবহারকারীর আলাদা এনক্রিপশন চাবি', en: 'A separate encryption key for every user' },
    trust2: { bn: 'আমাদের কর্মীরা আপনার কাগজ দেখতে পারেন না', en: 'Our staff cannot see your papers' },
    trust3: { bn: 'সরকারি পোর্টালের পাসওয়ার্ড আমরা কখনো চাই না', en: 'We never ask for government portal passwords' },
    trust4: { bn: 'এক ট্যাপে সব তথ্য স্থায়ীভাবে মুছে ফেলুন', en: 'Delete everything permanently in one tap' },
    trust5: { bn: 'কোনো বিজ্ঞাপন নেই, তথ্য কখনো বিক্রি হয় না', en: 'No ads, and your data is never sold' },
    pricingTitle: { bn: 'দাম', en: 'Pricing' },
    privacyTitle: { bn: 'গোপনীয়তা নীতি', en: 'Privacy policy' },
    termsTitle: { bn: 'ব্যবহারের শর্তাবলি', en: 'Terms of use' },
    draftNotice: {
        bn: 'খসড়া — প্রকাশের আগে আইনজীবী দিয়ে যাচাই করাতে হবে।',
        en: 'Draft — to be reviewed by a lawyer before launch.',
    },
    lastUpdated: { bn: 'সর্বশেষ হালনাগাদ: :date', en: 'Last updated: :date' },

    // Settings
    settingsTitle: { bn: 'সেটিংস', en: 'Settings' },
    more: { bn: 'আরও', en: 'More' },
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
