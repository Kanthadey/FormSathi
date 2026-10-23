<?php

/*
|--------------------------------------------------------------------------
| E-passport online application (epassport.gov.bd), version 1
|--------------------------------------------------------------------------
|
| DRAFT: the order and labels below are from public guides, not yet checked
| field by field against the live form (PRD open question). Leave
| verified_at empty until someone has gone through a real application,
| then bump the version for any change.
|
| Each field:
|   key      unique within this template
|   site     the label as printed on the official site (English)
|   profile  the master-profile key the value comes from (never the AI)
|   format   as_is | upper | date_dmy | digits | option_en
|   required whether an application cannot go ahead without it
|   help     optional {bn, en} hint shown on the card
| Labels in Bangla/English default to the profile field's own labels.
|
*/

return [
    'slug' => 'e-passport',
    'version' => 1,
    'name_bn' => 'ই-পাসপোর্ট আবেদন',
    'name_en' => 'E-passport application',
    'official_url' => 'https://www.epassport.gov.bd',
    'verified_at' => null,
    'sections' => [
        [
            'key' => 'personal',
            'bn' => 'ব্যক্তিগত তথ্য',
            'en' => 'Personal information',
            'fields' => [
                ['key' => 'full_name', 'site' => 'Full Name (as per NID/BRC)', 'profile' => 'name_en', 'format' => 'upper', 'required' => true,
                    'help' => ['bn' => 'এনআইডি/জন্ম নিবন্ধনে যেমন বানান, ঠিক তেমন।', 'en' => 'Spelled exactly as on your NID or birth registration.']],
                ['key' => 'given_name', 'site' => 'First Name (Given Name)', 'profile' => 'given_name_en', 'format' => 'upper', 'required' => true],
                ['key' => 'surname', 'site' => 'Last Name (Surname)', 'profile' => 'surname_en', 'format' => 'upper', 'required' => true],
                ['key' => 'gender', 'site' => 'Gender', 'profile' => 'gender', 'format' => 'option_en', 'required' => true],
                ['key' => 'date_of_birth', 'site' => 'Date of Birth', 'profile' => 'date_of_birth', 'format' => 'date_dmy', 'required' => true],
                ['key' => 'district_of_birth', 'site' => 'District of Birth', 'profile' => 'district_of_birth', 'format' => 'as_is', 'required' => true],
                ['key' => 'marital_status', 'site' => 'Marital Status', 'profile' => 'marital_status', 'format' => 'option_en', 'required' => true],
                ['key' => 'profession', 'site' => 'Profession', 'profile' => 'profession', 'format' => 'as_is', 'required' => true],
                ['key' => 'religion', 'site' => 'Religion', 'profile' => 'religion', 'format' => 'option_en', 'required' => false],
                ['key' => 'mobile', 'site' => 'Mobile Number', 'profile' => 'account_mobile', 'format' => 'digits', 'required' => true,
                    'bn' => 'মোবাইল নম্বর', 'en' => 'Mobile number'],
                ['key' => 'email', 'site' => 'Email', 'profile' => 'email', 'format' => 'as_is', 'required' => false],
            ],
        ],
        [
            'key' => 'present_address',
            'bn' => 'বর্তমান ঠিকানা',
            'en' => 'Present address',
            'fields' => [
                ['key' => 'present_district', 'site' => 'District', 'profile' => 'present_district', 'format' => 'as_is', 'required' => true],
                ['key' => 'present_police_station', 'site' => 'Police Station', 'profile' => 'present_police_station', 'format' => 'as_is', 'required' => true],
                ['key' => 'present_post_office', 'site' => 'Post Office', 'profile' => 'present_post_office', 'format' => 'as_is', 'required' => true],
                ['key' => 'present_post_code', 'site' => 'Post Code', 'profile' => 'present_post_code', 'format' => 'digits', 'required' => true],
                ['key' => 'present_village_road', 'site' => 'Village / House / Road', 'profile' => 'present_village_road', 'format' => 'as_is', 'required' => true],
            ],
        ],
        [
            'key' => 'permanent_address',
            'bn' => 'স্থায়ী ঠিকানা',
            'en' => 'Permanent address',
            'fields' => [
                ['key' => 'permanent_district', 'site' => 'District', 'profile' => 'permanent_district', 'format' => 'as_is', 'required' => true],
                ['key' => 'permanent_police_station', 'site' => 'Police Station', 'profile' => 'permanent_police_station', 'format' => 'as_is', 'required' => true],
                ['key' => 'permanent_post_office', 'site' => 'Post Office', 'profile' => 'permanent_post_office', 'format' => 'as_is', 'required' => true],
                ['key' => 'permanent_post_code', 'site' => 'Post Code', 'profile' => 'permanent_post_code', 'format' => 'digits', 'required' => true],
                ['key' => 'permanent_village_road', 'site' => 'Village / House / Road', 'profile' => 'permanent_village_road', 'format' => 'as_is', 'required' => true],
            ],
        ],
        [
            'key' => 'id_documents',
            'bn' => 'পরিচয়পত্র',
            'en' => 'ID documents',
            'fields' => [
                ['key' => 'nid', 'site' => 'National ID No.', 'profile' => 'nid_number', 'format' => 'digits', 'required' => false,
                    'help' => ['bn' => 'এনআইডি না থাকলে জন্ম নিবন্ধন নম্বর দিন।', 'en' => 'If you have no NID, give your birth registration number.']],
                ['key' => 'brc', 'site' => 'Birth Registration No.', 'profile' => 'birth_reg_number', 'format' => 'digits', 'required' => false],
                ['key' => 'previous_passport', 'site' => 'Previous Passport No.', 'profile' => 'passport_number', 'format' => 'upper', 'required' => false,
                    'help' => ['bn' => 'নবায়নের ক্ষেত্রে আগের পাসপোর্টের নম্বর।', 'en' => 'For a renewal: your old passport number.']],
            ],
        ],
        [
            'key' => 'parents',
            'bn' => 'পিতা-মাতার তথ্য',
            'en' => "Parents' information",
            'fields' => [
                ['key' => 'father_name', 'site' => "Father's Name", 'profile' => 'father_name_en', 'format' => 'upper', 'required' => true],
                ['key' => 'father_profession', 'site' => "Father's Profession", 'profile' => 'father_profession', 'format' => 'as_is', 'required' => true],
                ['key' => 'father_nid', 'site' => "Father's NID No.", 'profile' => 'father_nid_number', 'format' => 'digits', 'required' => false],
                ['key' => 'mother_name', 'site' => "Mother's Name", 'profile' => 'mother_name_en', 'format' => 'upper', 'required' => true],
                ['key' => 'mother_profession', 'site' => "Mother's Profession", 'profile' => 'mother_profession', 'format' => 'as_is', 'required' => true],
                ['key' => 'mother_nid', 'site' => "Mother's NID No.", 'profile' => 'mother_nid_number', 'format' => 'digits', 'required' => false],
            ],
        ],
        [
            'key' => 'spouse',
            'bn' => 'স্বামী/স্ত্রীর তথ্য',
            'en' => "Spouse's information",
            'fields' => [
                ['key' => 'spouse_name', 'site' => "Spouse's Name", 'profile' => 'spouse_name_en', 'format' => 'upper', 'required' => false,
                    'help' => ['bn' => 'শুধু বিবাহিত হলে।', 'en' => 'Only if married.']],
            ],
        ],
        [
            'key' => 'emergency',
            'bn' => 'জরুরি যোগাযোগ',
            'en' => 'Emergency contact',
            'fields' => [
                ['key' => 'emergency_name', 'site' => 'Name', 'profile' => 'emergency_contact_name', 'format' => 'upper', 'required' => true],
                ['key' => 'emergency_relation', 'site' => 'Relationship', 'profile' => 'emergency_contact_relation', 'format' => 'as_is', 'required' => true],
                ['key' => 'emergency_mobile', 'site' => 'Mobile Number', 'profile' => 'emergency_contact_mobile', 'format' => 'digits', 'required' => true],
                ['key' => 'emergency_address', 'site' => 'Address', 'profile' => 'emergency_contact_address', 'format' => 'as_is', 'required' => true],
            ],
        ],
    ],
];
