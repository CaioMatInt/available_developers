<?php

namespace App\Http\Requests\User;

use App\Enum\ProfileTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $developerProfileType = ProfileTypeEnum::Developer->name;
        $recruiterProfileType = ProfileTypeEnum::Recruiter->name;

        return [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'profile_type' => "required|string|in:${developerProfileType},${recruiterProfileType}",
        ];
    }
}
