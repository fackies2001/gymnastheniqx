<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;

class EmployeeService
{

    public function get_auth_employees($email)
    {
        return Employee::where('email', $email);
    }
}
