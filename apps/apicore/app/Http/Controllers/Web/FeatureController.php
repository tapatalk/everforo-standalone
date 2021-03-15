<?php

namespace App\Http\Controllers\Web;

use App\Models\GroupErc20Token;
use App\Repositories\AdminRepository;
use App\Repositories\GroupAdminRepository;
use Illuminate\Http\Request;
use App\Http\Requests\Web\CreateERC20TokenRequest;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Models\Erc20Token;
use App\Models\TokenWallet;
use App\Models\Features;
use App\Utils\Constants;
use App\Repositories\Erc20TokenRepo;
use App\Repositories\TokenTransactionRepo;
use App\Repositories\TokenWalletRepo;
use App\Repositories\AirdropRepo;
use App\Repositories\FeaturesRepo;
use App\Jobs\ERC20TokenJob;
use DB, Queue;

class FeatureController extends Controller
{
    private $_transformer;
    private $_group;
    private $_erc20tokenrepo;
//    private $_token;
    private $_tokenWalletRepo;
    private $_featuresRepo;

    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer, Erc20TokenRepo $Erc20TokenRepo, TokenWalletRepo  $TokenWalletRepo, FeaturesRepo $FeaturesRepo )
    {
        $this->_transformer = $transformer;
        $this->_erc20tokenrepo = $Erc20TokenRepo;
        $this->_tokenWalletRepo = $TokenWalletRepo;
        $this->_featuresRepo = $FeaturesRepo;
        $this->_group = config('app.group');
//        $this->_token = $Erc20TokenRepo->getGroupErc20Token($this->_group->id);
    }

    public function enableERC20Token(Request $request)
    {
        $user = $request->user();

        if ($this->_group->owner != $user->id) {
            return $this->_transformer->noPermission();
        }

        $token = $this->_erc20tokenrepo->enableGroupErc20Token($this->_group->id);

        return $this->_transformer->success(['token' => $token]);
    }


    public function getList(Request $request)
    {
        $list =  $this->_featuresRepo->getList( $this->_group->id);

        return $this->_transformer->success(['features' => $list]);
    }

    public function enableFeature(Request $request,FeaturesRepo $featuresRep, AdminRepository $adminRep, GroupAdminRepository $groupAdminRep)
    {
        $feature_id = $request->input('feature_id');
        $status = $request->input('status',1);
        $group_id = $this->_group->id;
        $user = $request->user();

        if (!$adminRep->isSuperAdmin($user->id)
            && !$groupAdminRep->checkGroupAdmin($this->_group->id, $user->id, 2)) {
            return $this->_transformer->noPermission();
        }

        if(!in_array($status,[0,1]) ){
            return  $this->_transformer->fail(40021,'status wrong');
        }

        $feature = Features::find($feature_id);
        if(!$feature){
            $this->_transformer->fail(40022, "feature not found");
        }

        $feature = $this->_featuresRepo->switchFeature($feature_id, $group_id, $status);
        $featuresRep->delFeatureCache($group_id);
        return $this->_transformer->success(['feature' => $feature]);
    }

    public function disableFeature(Request $request,FeaturesRepo $featuresRep)
    {
        $feature_id = $request->input('feature_id');
        $status = $request->input('status', 0);
        $group_id = $this->_group->id;

        if (!in_array($status, [0, 1])) {
            return $this->_transformer->fail(40021, 'status wrong');
        }

        $feature = Features::find($feature_id);
        if (!$feature) {
            $this->_transformer->fail(40022, "feature not found");
        }

        $feature = $this->_featuresRepo->switchFeature($feature_id, $group_id, $status);
        $featuresRep->delFeatureCache( $group_id);
        return $this->_transformer->success(['feature' => $feature]);
    }



}