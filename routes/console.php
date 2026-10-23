<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('documents:remind-expiry')->dailyAt('10:00')->timezone('Asia/Dhaka');
