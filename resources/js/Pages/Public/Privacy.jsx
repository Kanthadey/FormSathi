import LegalPage from './LegalPage';

// Plain-language draft built from the PRD's commitments. Must be reviewed
// against the final text of the Personal Data Protection Act, 2026 before launch.
const sections = [
    {
        title: { bn: 'আমরা কী তথ্য রাখি', en: 'What we keep' },
        points: [
            { bn: 'আপনার মোবাইল নম্বর ও পিনের হ্যাশ (আসল পিন নয়)।', en: 'Your mobile number and a hash of your PIN (never the PIN itself).' },
            { bn: 'আপনি যে কাগজের ছবি বা PDF আপলোড করেন।', en: 'The photos or PDFs of papers you upload.' },
            { bn: 'কাগজ থেকে নেওয়া ও আপনার লেখা প্রোফাইলের তথ্য, শিক্ষা ও কাজের অভিজ্ঞতা।', en: 'Profile details taken from your papers or typed by you, and your education and work history.' },
            { bn: 'কে কখন কোন কাগজ দেখেছে বা বদলেছে তার লগ।', en: 'A log of who viewed or changed which paper, and when.' },
            { bn: 'পেমেন্টের রেকর্ড (বিকাশ ট্রানজেকশন আইডি, পরিমাণ)। আপনার বিকাশ পিন আমরা কখনো দেখি না।', en: 'Payment records (bKash transaction ID, amount). We never see your bKash PIN.' },
        ],
    },
    {
        title: { bn: 'কীভাবে সুরক্ষিত রাখি', en: 'How we protect it' },
        points: [
            { bn: 'প্রতিটি ব্যবহারকারীর জন্য আলাদা চাবি দিয়ে AES-256 এনক্রিপশন; আসা-যাওয়ার পথে TLS।', en: 'AES-256 encryption with a separate key for every user; TLS in transit.' },
            { bn: 'কাগজ দেখতে আবার পিন লাগে; ৫ মিনিট ব্যবহার না হলে অ্যাপ লক হয়ে যায়।', en: 'Viewing a paper needs your PIN again; the app locks after 5 idle minutes.' },
            { bn: 'আমাদের কর্মীরা আপনার কাগজ বা প্রোফাইল দেখতে পারেন না।', en: 'Our staff cannot see your papers or profile.' },
            { bn: 'আইন অনুযায়ী বাংলাদেশের ভেতরে তথ্যের একটি কপি রাখা হয়।', en: 'A copy of the data is kept inside Bangladesh, as the law requires.' },
        ],
    },
    {
        title: { bn: 'এআই কীভাবে ব্যবহার হয়', en: 'How AI is used' },
        points: [
            { bn: 'পাসপোর্ট বা ভিসা ফর্মের কোনো তথ্য এআই তৈরি করে না — সব আসে আপনার প্রোফাইল থেকে।', en: 'AI never produces passport or visa form values — they come straight from your profile.' },
            { bn: 'এআই শুধু আপনার অনুরোধ বোঝা এবং সিভি/কভার লেটার লেখায় ব্যবহার হয়।', en: 'AI is used only to understand your requests and to write CVs and cover letters.' },
            { bn: 'এআই-তে পাঠানোর আগে নম্বর, ঠিকানা, ইমেইল, এনআইডি ও নাম সরিয়ে ফেলা হয়।', en: 'Numbers, addresses, emails, NID and your name are removed before anything is sent to the AI.' },
            { bn: 'চুক্তি অনুযায়ী এআই সেবাদাতা আপনার তথ্য রাখে না এবং তা দিয়ে মডেল প্রশিক্ষণ দেয় না।', en: 'Our contract with the AI provider means it neither keeps your data nor trains on it.' },
            { bn: 'কাগজ থেকে লেখা পড়া (OCR) আমাদের নিজস্ব সার্ভারেই হয়।', en: 'Reading text from papers (OCR) runs on our own servers.' },
        ],
    },
    {
        title: { bn: 'আমরা যা কখনো করি না', en: 'What we never do' },
        points: [
            { bn: 'আপনার তথ্য বিক্রি করা বা বিজ্ঞাপনে ব্যবহার করা।', en: 'Sell your data or use it for advertising.' },
            { bn: 'সরকারি পোর্টালের পাসওয়ার্ড চাওয়া, বা আপনার হয়ে লগইন বা আবেদন জমা দেওয়া।', en: 'Ask for government portal passwords, or log in or submit on your behalf.' },
            { bn: 'আপনার অনুমতি ছাড়া কাউকে আপনার তথ্য দেখানো।', en: 'Show your data to anyone without your permission.' },
        ],
    },
    {
        title: { bn: 'আপনার অধিকার', en: 'Your rights' },
        points: [
            { bn: 'যেকোনো সময় নিজের তথ্য দেখা ও সংশোধন করা।', en: 'See and correct your data at any time.' },
            { bn: 'সেটিংস থেকে এক ট্যাপে সব তথ্য ও কাগজ মুছে ফেলা; ব্যাকআপ থেকেও ৩০ দিনের মধ্যে মুছে যায়।', en: 'Delete all your data and papers in one tap from Settings; it is removed from backups within 30 days.' },
            { bn: 'শিশুর প্রোফাইলের জন্য অভিভাবকের সম্মতি লাগে।', en: 'A child’s profile needs a parent’s consent.' },
            { bn: 'তথ্য ফাঁস হলে আমরা আপনাকে জানাব।', en: 'If there is ever a data breach, we will tell you.' },
            { bn: 'প্রশ্ন বা অভিযোগ: অ্যাপের “সাহায্য” পাতা বা হেল্পলাইন।', en: 'Questions or complaints: the Help page in the app, or our helpline.' },
        ],
    },
];

export default function Privacy() {
    return <LegalPage titleKey="privacyTitle" updated="2026-10-24" sections={sections} />;
}
