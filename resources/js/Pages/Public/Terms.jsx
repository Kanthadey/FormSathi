import LegalPage from './LegalPage';

// Plain-language draft. Must be reviewed by a lawyer before launch.
const sections = [
    {
        title: { bn: 'ফর্মসাথী কী', en: 'What FormSathi is' },
        points: [
            { bn: 'ফর্মসাথী একটি বেসরকারি সেবা; এটি কোনো সরকারি সংস্থা নয় এবং কোনো সরকারি সংস্থার পক্ষে কাজ করে না।', en: 'FormSathi is a private service. It is not a government body and does not act for one.' },
            { bn: 'আমরা আপনার তথ্য ফর্মের ক্রমে সাজিয়ে দিই। আবেদন জমা দেন আপনি নিজে, অফিসিয়াল সাইটে।', en: 'We lay out your details in each form’s order. You submit the application yourself, on the official site.' },
            { bn: 'সরকারি ফি আমাদের মাধ্যমে পরিশোধ হয় না।', en: 'Government fees are not paid through us.' },
        ],
    },
    {
        title: { bn: 'আপনার দায়িত্ব', en: 'Your responsibilities' },
        points: [
            { bn: 'জমা দেওয়ার আগে প্রতিটি তথ্য নিজে মিলিয়ে দেখুন। সরকারি ফর্ম বদলাতে পারে।', en: 'Check every detail yourself before submitting. Government forms can change.' },
            { bn: 'শুধু নিজের, অথবা অনুমতি আছে এমন পরিবারের সদস্যের কাগজ আপলোড করুন।', en: 'Upload only your own papers, or those of family members who have agreed.' },
            { bn: 'পিন কাউকে বলবেন না। অন্য কেউ আপনার ফোনে লগইন করলে আমাদের জানান।', en: 'Never share your PIN. Tell us if someone else logs in on your phone.' },
            { bn: 'সিভি ও কভার লেটার পড়ে দেখুন; আপনার নামে যা পাঠাবেন তার দায় আপনার।', en: 'Read your CV and cover letter; you are responsible for what you send in your name.' },
        ],
    },
    {
        title: { bn: 'পেমেন্ট ও ফেরত', en: 'Payment and refunds' },
        points: [
            { bn: 'পেমেন্ট বিকাশের মাধ্যমে; সফল হলে সাথে সাথে প্ল্যান চালু হয় এবং এসএমএসে রসিদ যায়।', en: 'Payment is through bKash; your plan starts as soon as payment succeeds, with an SMS receipt.' },
            { bn: 'টাকা কাটা হলেও প্ল্যান চালু না হলে TrxID সহ হেল্পলাইনে জানান — যাচাই করে চালু বা ফেরত দেওয়া হবে।', en: 'If money was taken but your plan didn’t start, contact the helpline with the TrxID — we will check and activate or refund.' },
            { bn: 'এআই ব্যর্থ হলে কোনো ফর্ম সহায়তা খরচ হয় না।', en: 'If the AI fails, no form assist is used.' },
        ],
    },
    {
        title: { bn: 'সেবার সীমা', en: 'Limits of the service' },
        points: [
            { bn: 'আমরা সর্বোচ্চ যত্ন নিই, তবে সরকারি দপ্তর কোনো আবেদন গ্রহণ করবে কিনা তার নিশ্চয়তা দিতে পারি না।', en: 'We take great care, but we cannot guarantee that any office will accept an application.' },
            { bn: 'অপব্যবহার, অন্যের তথ্য দিয়ে প্রতারণা বা জাল কাগজ আপলোড করলে অ্যাকাউন্ট বন্ধ করা হবে।', en: 'Accounts used for fraud, someone else’s identity, or forged papers will be closed.' },
            { bn: 'এই শর্ত বাংলাদেশের আইন অনুযায়ী পরিচালিত।', en: 'These terms are governed by the laws of Bangladesh.' },
        ],
    },
];

export default function Terms() {
    return <LegalPage titleKey="termsTitle" updated="2026-10-24" sections={sections} />;
}
