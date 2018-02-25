<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 25/02/18
 * Time: 12:51
 */

namespace app\models\tryit;


class Speaker extends Model
{
    public $name;
    public $bio;
    public $picture;
    public $company;
    public $personal_web;
    public $twitter_profile;
    public $facebook_profile;
    public $linkedin_profile;
    public $googleplus_profile;
    public $github_profile;
    public $gitlab_profile;


    function rules()
    {
        return [
            [['name', 'bio', 'company'], 'string'],
            [['picture', 'personal_web', 'twitter_profile', 'facebook_profile', 'linkedin_profile', 'googleplus_profile', 'github_profile', 'gitlab_profile'], 'url'],
        ];
    }

}