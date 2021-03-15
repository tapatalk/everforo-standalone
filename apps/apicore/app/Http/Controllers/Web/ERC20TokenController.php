<?php

namespace App\Http\Controllers\Web;

use App\Models\GroupErc20Token;
use Illuminate\Http\Request;
use App\Http\Requests\Web\CreateERC20TokenRequest;
use App\Http\Controllers\Controller;
use App\Utils\Transformer;
use App\Models\Erc20Token;
use App\Models\Orders;
use App\Models\TokenWallet;
use App\WriteLog;
use App\Utils\Constants;
use App\Repositories\Erc20TokenRepo;
use App\Repositories\TokenTransactionRepo;
use App\Repositories\TokenWalletRepo;
use App\Repositories\OrdersRepo;
use App\Repositories\AirdropRepo;
use App\Jobs\ERC20TokenJob;
use DB, Queue;

class ERC20TokenController extends Controller
{
    private $_transformer;
    private $_group;
    private $_erc20tokenrepo;
//    private $_token;
    private $_tokenWalletRepo;

    /**
     * ERC20TokenController constructor.
     * @param
     */
    public function __construct(Transformer $transformer, Erc20TokenRepo $Erc20TokenRepo, TokenWalletRepo  $TokenWalletRepo )
    {
        $this->_transformer = $transformer;
        $this->_erc20tokenrepo = $Erc20TokenRepo;
        $this->_tokenWalletRepo = $TokenWalletRepo;
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

    /**
     *
     * @param CreateERC20TokenRequest
     * @return \Illuminate\Http\Response
     */
    public function createERC20Token(CreateERC20TokenRequest $request)
    {

        $name = $request->input('name');
        $symbol = $request->input('symbol');
        $balance = $request->input('balance');
        $logo = $request->input('logo');
        $decimal = $request->input('decimal') ? (int)$request->input('decimal') : 0 ;

        $user = $request->user();

        //get balance value should be save in table
        $balance = TokenTransactionRepo::getTableBalance($balance,$decimal);

        try {

            DB::beginTransaction();

            $grouptoken = GroupErc20Token::where('group_id', $this->_group->id)
                ->whereNull('deleted_at')
                ->first();

            if ($grouptoken) {
                throw new \Exception('you have one token in this group');
            }

            if (!empty($grouptoken->token_id) ) {
                throw new \Exception('only allow create one erc20token');
            }

            //create token
            $erc20token = Erc20Token::create([
                'name'=>$name,
                'symbol'=>$symbol,
                'logo' => $logo,
                'decimal' => $decimal,
            ]);

            if(!$erc20token || empty($erc20token->id)){
                throw new \Exception('try again later');
            }

            //create token group relationship
            $token = GroupErc20Token::create([
                'group_id'  => $this->_group->id,
                'status'    => 1,
                'token_id'  => $erc20token->id,
                'blockchain_balance'  => $balance,
            ]);

            if(!$token || empty($token->id)){
                throw new \Exception('try again later');
            }

//create wallet wait for paypal success
//            $token_wallet = TokenWallet::create([
//                'group_id'  => $this->_group->id,
//                'token_id'  => $erc20token->id,
//                'balance'   => $balance
//                ]);
//
//            if(!$token_wallet || !isset($token_wallet->id)){
//                throw new \Exception('please try again later');
//            }

            $OrdersRepo = new OrdersRepo();
            $order = $OrdersRepo->createOrderByProduct($user->id,  $this->_group->id, Constants::PRODUCT_ID_CREATE_TOKEN, $request->server('HTTP_USER_AGENT'), $erc20token->id );

            if(empty($order->id) || !empty($order['error'])){
                throw new \Exception(empty($order['error']) ? 'error' : $order['error']);
                return $this->_transformer->fail('40054',empty($order['error']) ? 'error' : $order['error'] );
            }


            if(!$order || empty($order->id)){
                throw new \Exception('wait for order confirm');
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->_transformer->fail('40004',$e->getMessage());
        }

//            $token->balance = $token_wallet->balance;

//        if (env('APP_ENV') === 'local') {
//            $result = exec('php ' .  base_path('script/test.php'));
//        } else {
//            $cmd = 'python3 ' .  base_path('script/create_erc20token.py') . " " . $grouptoken->id . " &";
//            \Log::error($cmd);
//            exec( $cmd );
//        }

        return $this->_transformer->success(['order'=>$order]);

    }

    public function createERC20TokenImport(Request $request)
    {
//        $user = $request->user();

//        if ($this->_group->owner != $user->id) {
//            return $this->_transformer->noPermission();
//        }

        $token_id = $request->input('token_id');

        $erc20token = Erc20Token::find($token_id);

        if(empty($erc20token)  ){
            return $this->_transformer->fail('40006','token missing');
        }

        if(empty($erc20token->allow_import) || $erc20token->allow_import != 1 ){
            return $this->_transformer->fail('40007','not allow import');
        }

        $grouptoken = $this->_erc20tokenrepo->enableGroupErc20TokenImport($this->_group->id, $token_id);

        if($grouptoken['is_new']){
            $grouptoken = $grouptoken['token'];
            $cmd = 'python3 ' .  base_path('script/create_erc20token_for_import.py') . " " . $grouptoken->id . " > " . base_path('storage/logs/create_erc20token_for_import.error.log') . " 2>&1 &";
            \Log::error($cmd);
            if (env('APP_ENV') === 'local') {
//            $result = exec('php ' .  base_path('script/test.php'));
            } else {
                exec( $cmd );
            }
        } else {
            $grouptoken = $grouptoken['token'];
        }
        
        $grouptoken = GroupErc20Token::find($grouptoken->id );

        try{
            $this->_tokenWalletRepo->createWallet(0,$this->_group->id,$erc20token->id);
        }catch(Exception $e ){
            // todo log failed
        }

        if(!empty($grouptoken) && !empty($grouptoken->public_key)){
            unset($grouptoken->private_key);
            return $this->_transformer->success(['token' => $grouptoken]);
        } else {
            return $this->_transformer->fail('40005','enable failed, try again later');
        }
    }

    public function getERC20TokenImportList($page = 0){

//        $page = int($request->input('page')) ? int($request->input('page')) : 0;
        $tokenlist = $this->_erc20tokenrepo->getErc20TokenImportList($page);

        if(empty($tokenlist)){
            return $this->_transformer->fail('40008','try again later');
        }
        return $this->_transformer->success(['tokenlist'=>$tokenlist]);
    }

    public function testERC20Token(Request $request , Erc20TokenRepo $Erc20TokenRepo , TokenWalletRepo $TokenWalletRepo , TokenTransactionRepo $TokenTransactionRepo )
    {
//        $a = '{"group_id":268,"airdrop_job_id":1,"user_id":352,"job_type":"airdrop"}';
//        $b = new ERC20TokenJob([]);
//        $b->handle();
//        $b::verifyERC20TokenImportToken();


        WriteLog::info('test',[],'testlog');


//        var_dump('test');exit;

        $b = new ERC20TokenJob([
                'group_id'          =>  379,
                'airdrop_job_id'    =>  132,
                'job_type'          =>  'onetime_airdrop'
            ]);
        $b->handle($Erc20TokenRepo,$TokenWalletRepo,$TokenTransactionRepo);


//        ERC20TokenJob::verifyERC20TokenImportToken();
    }


    public function deleteImportedERC20Token() 
    {    
        $result = $this->_erc20tokenrepo->deleteImported($this->_group->id);       

        return $this->_transformer->success(['status'=>1]);
    }


    public function getMyAssets(Request $request){
        $user = $request->user();

        if(!$user->id){
            return $this->_transformer->fail('40014','no user');
        }

        $wallets = $this->_tokenWalletRepo->getAllERC20TokenWallet($user->id);


        return $this->_transformer->success(['wallets'=>$wallets]);
    }

}