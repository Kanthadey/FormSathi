<?php

// Only the rules the app uses; anything else falls back to English.
return [
    'required' => ':attribute দিতে হবে।',
    'digits' => ':attribute অবশ্যই :digits সংখ্যার হতে হবে।',
    'confirmed' => 'দুইবার দেওয়া :attribute মেলেনি।',
    'file' => ':attribute একটি ফাইল হতে হবে।',
    'mimes' => ':attribute এর ধরন হতে হবে: :values।',
    'max' => [
        'file' => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
    ],
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'date' => ':attribute সঠিক তারিখ নয়।',
    'string' => ':attribute লেখা হতে হবে।',
    'array' => ':attribute সঠিক নয়।',
    'required_if' => ':attribute দিতে হবে।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',

    'attributes' => [
        'pin' => 'পিন',
        'mobile' => 'মোবাইল নম্বর',
        'code' => 'কোড',
        'file' => 'ফাইল',
        'type' => 'কাগজের ধরন',
        'category' => 'ফোল্ডার',
        'expires_at' => 'মেয়াদ শেষের তারিখ',
        'value' => 'তথ্য',
    ],
];
