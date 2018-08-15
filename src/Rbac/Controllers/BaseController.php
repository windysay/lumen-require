<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 10:37
 */

namespace Yunhan\Rbac\Controllers;

use Laravel\Lumen\Routing\Controller;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;

class BaseController extends Controller
{
    /** @var OutputDataFormatContract $output */
    protected $output;
}