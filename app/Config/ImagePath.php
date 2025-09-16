<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
class ImagePath extends BaseConfig
{
    public string $studentPassportPath = 'uploads/students/passports/';

    public string $studentFullimagePath = 'uploads/students/full_images/';

    public string $cacCertificatePath = 'uploads/contractors_cac/';

    public string $paymentVoucherPath = 'uploads/payment_voucher/';

    public string $retireAdvancePath = 'uploads/retire_salary_advance/';

    public string $userPassportPath = 'uploads/users_avatar/';
}