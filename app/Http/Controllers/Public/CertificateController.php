<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Academy\CourseEnrolment;

class CertificateController extends Controller
{
    public function __invoke(string $code)
    {
        $e = CourseEnrolment::query()->where('certificate_code', $code)->with('course', 'user')->first();

        return view('public.certificate-verify', ['enrolment' => $e, 'code' => $code]);
    }
}
