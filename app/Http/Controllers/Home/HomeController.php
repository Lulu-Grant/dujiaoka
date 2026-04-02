<?php

namespace App\Http\Controllers\Home;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\BaseController;
use App\Models\Pay;
use App\Service\InstallationService;
use Germey\Geetest\Geetest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends BaseController
{

    /**
     * 商品服务层.
     * @var \App\Service\PayService
     */
    private $goodsService;

    /**
     * 支付服务层
     * @var \App\Service\PayService
     */
    private $payService;

    /**
     * @var \App\Service\InstallationService
     */
    private $installationService;

    public function __construct()
    {
        $this->goodsService = app('Service\GoodsService');
        $this->payService = app('Service\PayService');
        $this->installationService = app(InstallationService::class);
    }

    /**
     * 首页.
     *
     * @param Request $request
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function index(Request $request)
    {
        $goods = $this->goodsService->withGroup();
        return $this->render('static_pages/home', ['data' => $goods], __('dujiaoka.page-title.home'));
    }

    /**
     * 商品详情
     *
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function buy(int $id)
    {
        try {
            $goods = $this->goodsService->detail($id);
            $this->goodsService->validatorGoodsStatus($goods);
            // 有没有优惠码可以展示
            if (count($goods->coupon)) {
                $goods->open_coupon = 1;
            }
            $formatGoods = $this->goodsService->format($goods);
            // 加载支付方式.
            $client = Pay::PAY_CLIENT_PC;
            if (app('Jenssegers\Agent')->isMobile()) {
                $client = Pay::PAY_CLIENT_MOBILE;
            }
            $formatGoods->payways = $this->payService->pays($client);
            return $this->render('static_pages/buy', $formatGoods, $formatGoods->gd_name);
        } catch (RuleValidationException $ruleValidationException) {
            return $this->err($ruleValidationException->getMessage());
        }

    }

    /**
     * 极验行为验证
     *
     * @param Request $request
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function geetest(Request $request)
    {
        $data = [
            'user_id' => @Auth::user()?@Auth::user()->id:'UnLoginUser',
            'client_type' => 'web',
            'ip_address' => \Illuminate\Support\Facades\Request::ip()
        ];
        $status = Geetest::preProcess($data);
        session()->put('gtserver', $status);
        session()->put('user_id', $data['user_id']);
        return Geetest::getResponseStr();
    }

    /**
     * 安装页面
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function install(Request $request)
    {
        return view('common/install');
    }

    /**
     * 执行安装
     *
     * @param Request $request
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function doInstall(Request $request)
    {
        try {
            $request->validate([
                'db_host' => 'required|string',
                'db_port' => 'required|string',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
                'redis_host' => 'required|string',
                'redis_password' => 'nullable|string',
                'redis_port' => 'required|string',
                'title' => 'required|string',
                'app_url' => 'required|string',
                'admin_path' => 'required|string',
                'admin_username' => 'required|string|min:3|max:120',
                'admin_password' => 'required|string|min:8|same:admin_password_confirmation',
                'admin_password_confirmation' => 'required|string',
            ]);

            $this->installationService->install($request->all());
            return 'success';
        } catch (\RedisException $exception) {
            return 'Redis配置错误 :' . $exception->getMessage();
        } catch (QueryException $exception) {
            return '数据库配置错误 :' . $exception->getMessage();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }


}
