<?php

/*
|--------------------------------------------------------------------------
| FormSathi product configuration
|--------------------------------------------------------------------------
|
| Document types, profile fields and the rules that tie them together.
| Labels are kept in both Bangla and English so the frontend can switch
| language without another round trip.
|
*/

return [

    /*
    | Proxies in front of the server (Cloudflare, a load balancer), as a
    | comma-separated list of IPs/CIDRs, or "*" for any. Leave empty when
    | Nginx faces the internet directly. Without this behind a proxy, every
    | visitor looks like the proxy: rate limits and access logs break.
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'company' => [
        'name' => env('COMPANY_NAME', 'FormSathi'),
        'address' => env('COMPANY_ADDRESS', 'Dhaka, Bangladesh'),
        'helpline' => env('COMPANY_HELPLINE', '+880 1XXX-XXXXXX'),
    ],

    'otp' => [
        'length' => 6,
        'ttl_minutes' => 5,
        'max_attempts' => 5,
        // Sends allowed per mobile number per window.
        'max_sends' => 3,
        'send_window_minutes' => 15,
    ],

    'pin' => [
        'max_attempts' => 5,
        'lockout_minutes' => 15,
        // Lock the app after this many idle minutes (PRD: 5).
        'idle_lock_minutes' => 5,
        // How long a PIN entry lets the user view papers.
        'view_papers_minutes' => 5,
    ],

    'uploads' => [
        'max_kb' => 10240,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    ],

    'free_plan' => [
        'max_documents' => 10,
        'form_assists_per_month' => 3,
    ],

    'premium_plan' => [
        'max_documents' => 100,
    ],

    // Prices in whole taka (PRD: proposals to test with users before launch).
    'products' => [
        'form_pack' => ['bn' => 'ফর্ম প্যাক', 'en' => 'Form pack', 'amount' => 99, 'credits' => 1],
        'premium_month' => ['bn' => 'প্রিমিয়াম (১ মাস)', 'en' => 'Premium (1 month)', 'amount' => 199, 'months' => 1],
        'premium_year' => ['bn' => 'প্রিমিয়াম (১ বছর)', 'en' => 'Premium (1 year)', 'amount' => 999, 'months' => 12],
    ],

    /*
    | Career record for the job kit (F10). Each kind lists its fields; the
    | first field is the one shown as the item's title.
    */
    'career' => [
        'education' => ['bn' => 'শিক্ষা', 'en' => 'Education', 'fields' => [
            'degree' => ['bn' => 'ডিগ্রি / পরীক্ষা', 'en' => 'Degree / exam', 'type' => 'text', 'required' => true],
            'institution' => ['bn' => 'প্রতিষ্ঠান', 'en' => 'Institution', 'type' => 'text', 'required' => true],
            'subject' => ['bn' => 'বিষয় / বিভাগ', 'en' => 'Subject / group', 'type' => 'text'],
            'year' => ['bn' => 'পাসের সাল', 'en' => 'Year passed', 'type' => 'number'],
            'result' => ['bn' => 'ফলাফল', 'en' => 'Result', 'type' => 'text'],
        ]],
        'work' => ['bn' => 'কাজের অভিজ্ঞতা', 'en' => 'Work experience', 'fields' => [
            'title' => ['bn' => 'পদবি', 'en' => 'Job title', 'type' => 'text', 'required' => true],
            'employer' => ['bn' => 'প্রতিষ্ঠান', 'en' => 'Employer', 'type' => 'text', 'required' => true],
            'location' => ['bn' => 'স্থান', 'en' => 'Location', 'type' => 'text'],
            'start' => ['bn' => 'শুরু', 'en' => 'Start', 'type' => 'month', 'required' => true],
            'end' => ['bn' => 'শেষ (চলমান হলে খালি)', 'en' => 'End (blank if current)', 'type' => 'month'],
            'description' => ['bn' => 'কী কাজ করেছেন', 'en' => 'What you did', 'type' => 'textarea'],
        ]],
        'skill' => ['bn' => 'দক্ষতা', 'en' => 'Skills', 'fields' => [
            'name' => ['bn' => 'দক্ষতা', 'en' => 'Skill', 'type' => 'text', 'required' => true],
        ]],
        'language' => ['bn' => 'ভাষা', 'en' => 'Languages', 'fields' => [
            'name' => ['bn' => 'ভাষা', 'en' => 'Language', 'type' => 'text', 'required' => true],
            'level' => ['bn' => 'দক্ষতার মাত্রা', 'en' => 'Level', 'type' => 'select', 'options' => [
                'native' => ['bn' => 'মাতৃভাষা', 'en' => 'Native'],
                'fluent' => ['bn' => 'সাবলীল', 'en' => 'Fluent'],
                'good' => ['bn' => 'ভালো', 'en' => 'Good'],
                'basic' => ['bn' => 'প্রাথমিক', 'en' => 'Basic'],
            ]],
        ]],
        'training' => ['bn' => 'প্রশিক্ষণ', 'en' => 'Training', 'fields' => [
            'name' => ['bn' => 'প্রশিক্ষণের নাম', 'en' => 'Training', 'type' => 'text', 'required' => true],
            'provider' => ['bn' => 'প্রতিষ্ঠান', 'en' => 'Provider', 'type' => 'text'],
            'year' => ['bn' => 'সাল', 'en' => 'Year', 'type' => 'number'],
        ]],
    ],

    'ocr' => [
        'tesseract' => env('TESSERACT_PATH', 'tesseract'),
        'languages' => 'ben+eng',
    ],

    'categories' => [
        'identity' => ['bn' => 'পরিচয়', 'en' => 'Identity'],
        'education' => ['bn' => 'শিক্ষা', 'en' => 'Education'],
        'travel' => ['bn' => 'ভ্রমণ', 'en' => 'Travel'],
        'work' => ['bn' => 'কাজ', 'en' => 'Work'],
    ],

    /*
    | Each type lists the fields read from that paper. "expiry_field" names
    | the field whose value becomes the document's expiry date, and
    | "remind_months_before" sets when the SMS reminder goes out.
    */
    'document_types' => [
        'nid' => [
            'bn' => 'জাতীয় পরিচয়পত্র (NID)', 'en' => 'National ID (NID)',
            'category' => 'identity',
            'fields' => ['name_bn', 'name_en', 'father_name_bn', 'mother_name_bn', 'date_of_birth', 'nid_number', 'permanent_address'],
        ],
        'birth_registration' => [
            'bn' => 'জন্ম নিবন্ধন', 'en' => 'Birth registration',
            'category' => 'identity',
            'fields' => ['birth_reg_number', 'name_bn', 'name_en', 'date_of_birth', 'father_name_bn', 'father_name_en', 'mother_name_bn', 'mother_name_en', 'permanent_address'],
        ],
        'photo' => [
            'bn' => 'ছবি', 'en' => 'Photo',
            'category' => 'identity',
            'fields' => [],
        ],
        'passport' => [
            'bn' => 'পাসপোর্ট', 'en' => 'Passport',
            'category' => 'travel',
            'fields' => ['passport_number', 'name_en', 'date_of_birth', 'passport_issue_date', 'passport_expiry_date'],
            'expiry_field' => 'passport_expiry_date',
            'remind_months_before' => 6,
        ],
        'visa' => [
            'bn' => 'ভিসা', 'en' => 'Visa',
            'category' => 'travel',
            'fields' => ['passport_number', 'name_en', 'visa_expiry_date'],
            'expiry_field' => 'visa_expiry_date',
            'remind_months_before' => 1,
        ],
        'ssc' => [
            'bn' => 'এসএসসি সনদ', 'en' => 'SSC certificate',
            'category' => 'education',
            'fields' => ['name_en', 'father_name_en', 'mother_name_en', 'date_of_birth'],
        ],
        'hsc' => [
            'bn' => 'এইচএসসি সনদ', 'en' => 'HSC certificate',
            'category' => 'education',
            'fields' => ['name_en', 'father_name_en', 'mother_name_en'],
        ],
        'degree' => [
            'bn' => 'স্নাতক/অন্যান্য সনদ', 'en' => 'Degree / other certificate',
            'category' => 'education',
            'fields' => ['name_en'],
        ],
        'experience_letter' => [
            'bn' => 'অভিজ্ঞতার সনদ', 'en' => 'Experience letter',
            'category' => 'work',
            'fields' => ['name_en'],
        ],
        'other' => [
            'bn' => 'অন্যান্য', 'en' => 'Other',
            'category' => null,
            'fields' => [],
        ],
    ],

    /*
    | Every field a document can hold. "profile" fields make up the master
    | profile; "compare" fields are checked for mismatches across papers
    | (F6); "sensitive" fields are masked before anything reaches the AI.
    | "core" fields count towards profile completeness.
    */
    'fields' => [
        'name_bn' => ['bn' => 'নাম (বাংলা)', 'en' => 'Name (Bangla)', 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'name_en' => ['bn' => 'নাম (ইংরেজি)', 'en' => 'Name (English)', 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'father_name_bn' => ['bn' => 'পিতার নাম (বাংলা)', 'en' => "Father's name (Bangla)", 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'father_name_en' => ['bn' => 'পিতার নাম (ইংরেজি)', 'en' => "Father's name (English)", 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'mother_name_bn' => ['bn' => 'মাতার নাম (বাংলা)', 'en' => "Mother's name (Bangla)", 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'mother_name_en' => ['bn' => 'মাতার নাম (ইংরেজি)', 'en' => "Mother's name (English)", 'type' => 'text', 'profile' => true, 'compare' => true, 'core' => true],
        'date_of_birth' => ['bn' => 'জন্ম তারিখ', 'en' => 'Date of birth', 'type' => 'date', 'profile' => true, 'compare' => true, 'core' => true],
        'nid_number' => ['bn' => 'এনআইডি নম্বর', 'en' => 'NID number', 'type' => 'number', 'profile' => true, 'sensitive' => true, 'core' => true],
        'birth_reg_number' => ['bn' => 'জন্ম নিবন্ধন নম্বর', 'en' => 'Birth registration number', 'type' => 'number', 'profile' => true, 'sensitive' => true],
        'passport_number' => ['bn' => 'পাসপোর্ট নম্বর', 'en' => 'Passport number', 'type' => 'text', 'profile' => true, 'sensitive' => true],
        'present_address' => ['bn' => 'বর্তমান ঠিকানা', 'en' => 'Present address', 'type' => 'textarea', 'profile' => true, 'sensitive' => true, 'core' => true],
        'permanent_address' => ['bn' => 'স্থায়ী ঠিকানা', 'en' => 'Permanent address', 'type' => 'textarea', 'profile' => true, 'sensitive' => true, 'core' => true],
        'profession' => ['bn' => 'পেশা', 'en' => 'Profession', 'type' => 'text', 'profile' => true, 'core' => true],
        'emergency_contact_name' => ['bn' => 'জরুরি যোগাযোগ (নাম)', 'en' => 'Emergency contact name', 'type' => 'text', 'profile' => true, 'core' => true],
        'emergency_contact_mobile' => ['bn' => 'জরুরি যোগাযোগ (মোবাইল)', 'en' => 'Emergency contact mobile', 'type' => 'tel', 'profile' => true, 'sensitive' => true, 'core' => true],

        // Needed by the e-passport form. Names are split by the user, never by guesswork.
        'given_name_en' => ['bn' => 'নামের প্রথম অংশ (ইংরেজি)', 'en' => 'Given name (English)', 'type' => 'text', 'profile' => true],
        'surname_en' => ['bn' => 'নামের শেষ অংশ / পদবি (ইংরেজি)', 'en' => 'Surname (English)', 'type' => 'text', 'profile' => true],
        'gender' => ['bn' => 'লিঙ্গ', 'en' => 'Gender', 'type' => 'select', 'profile' => true, 'options' => [
            'male' => ['bn' => 'পুরুষ', 'en' => 'Male'],
            'female' => ['bn' => 'নারী', 'en' => 'Female'],
            'third_gender' => ['bn' => 'তৃতীয় লিঙ্গ', 'en' => 'Third Gender'],
        ]],
        'religion' => ['bn' => 'ধর্ম', 'en' => 'Religion', 'type' => 'select', 'profile' => true, 'options' => [
            'islam' => ['bn' => 'ইসলাম', 'en' => 'Islam'],
            'hinduism' => ['bn' => 'হিন্দু', 'en' => 'Hinduism'],
            'buddhism' => ['bn' => 'বৌদ্ধ', 'en' => 'Buddhism'],
            'christianity' => ['bn' => 'খ্রিস্টান', 'en' => 'Christianity'],
            'other' => ['bn' => 'অন্যান্য', 'en' => 'Other'],
        ]],
        'marital_status' => ['bn' => 'বৈবাহিক অবস্থা', 'en' => 'Marital status', 'type' => 'select', 'profile' => true, 'options' => [
            'single' => ['bn' => 'অবিবাহিত', 'en' => 'Single'],
            'married' => ['bn' => 'বিবাহিত', 'en' => 'Married'],
            'divorced' => ['bn' => 'তালাকপ্রাপ্ত', 'en' => 'Divorced'],
            'widowed' => ['bn' => 'বিধবা / বিপত্নীক', 'en' => 'Widowed'],
        ]],
        'district_of_birth' => ['bn' => 'জন্মস্থান (জেলা)', 'en' => 'District of birth', 'type' => 'text', 'profile' => true],
        'email' => ['bn' => 'ইমেইল', 'en' => 'Email', 'type' => 'email', 'profile' => true, 'sensitive' => true],
        'father_profession' => ['bn' => 'পিতার পেশা', 'en' => "Father's profession", 'type' => 'text', 'profile' => true],
        'father_nid_number' => ['bn' => 'পিতার এনআইডি নম্বর', 'en' => "Father's NID number", 'type' => 'number', 'profile' => true, 'sensitive' => true],
        'mother_profession' => ['bn' => 'মাতার পেশা', 'en' => "Mother's profession", 'type' => 'text', 'profile' => true],
        'mother_nid_number' => ['bn' => 'মাতার এনআইডি নম্বর', 'en' => "Mother's NID number", 'type' => 'number', 'profile' => true, 'sensitive' => true],
        'spouse_name_en' => ['bn' => 'স্বামী/স্ত্রীর নাম (ইংরেজি)', 'en' => "Spouse's name (English)", 'type' => 'text', 'profile' => true],
        'emergency_contact_relation' => ['bn' => 'জরুরি যোগাযোগ (সম্পর্ক)', 'en' => 'Emergency contact relationship', 'type' => 'text', 'profile' => true],
        'emergency_contact_address' => ['bn' => 'জরুরি যোগাযোগ (ঠিকানা)', 'en' => 'Emergency contact address', 'type' => 'textarea', 'profile' => true, 'sensitive' => true],

        // Address in the parts online forms ask for.
        'present_district' => ['bn' => 'বর্তমান ঠিকানা: জেলা', 'en' => 'Present address: district', 'type' => 'text', 'profile' => true],
        'present_police_station' => ['bn' => 'বর্তমান ঠিকানা: থানা', 'en' => 'Present address: police station', 'type' => 'text', 'profile' => true],
        'present_post_office' => ['bn' => 'বর্তমান ঠিকানা: ডাকঘর', 'en' => 'Present address: post office', 'type' => 'text', 'profile' => true],
        'present_post_code' => ['bn' => 'বর্তমান ঠিকানা: পোস্ট কোড', 'en' => 'Present address: post code', 'type' => 'number', 'profile' => true],
        'present_village_road' => ['bn' => 'বর্তমান ঠিকানা: গ্রাম/বাসা/রোড', 'en' => 'Present address: village / house / road', 'type' => 'text', 'profile' => true, 'sensitive' => true],
        'permanent_district' => ['bn' => 'স্থায়ী ঠিকানা: জেলা', 'en' => 'Permanent address: district', 'type' => 'text', 'profile' => true],
        'permanent_police_station' => ['bn' => 'স্থায়ী ঠিকানা: থানা', 'en' => 'Permanent address: police station', 'type' => 'text', 'profile' => true],
        'permanent_post_office' => ['bn' => 'স্থায়ী ঠিকানা: ডাকঘর', 'en' => 'Permanent address: post office', 'type' => 'text', 'profile' => true],
        'permanent_post_code' => ['bn' => 'স্থায়ী ঠিকানা: পোস্ট কোড', 'en' => 'Permanent address: post code', 'type' => 'number', 'profile' => true],
        'permanent_village_road' => ['bn' => 'স্থায়ী ঠিকানা: গ্রাম/বাসা/রোড', 'en' => 'Permanent address: village / house / road', 'type' => 'text', 'profile' => true, 'sensitive' => true],

        // Document-only fields: kept on the paper, not in the master profile.
        'passport_issue_date' => ['bn' => 'পাসপোর্ট ইস্যুর তারিখ', 'en' => 'Passport issue date', 'type' => 'date', 'profile' => false],
        'passport_expiry_date' => ['bn' => 'পাসপোর্টের মেয়াদ শেষ', 'en' => 'Passport expiry date', 'type' => 'date', 'profile' => false],
        'visa_expiry_date' => ['bn' => 'ভিসার মেয়াদ শেষ', 'en' => 'Visa expiry date', 'type' => 'date', 'profile' => false],
    ],

];
