<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\BlockUsersRequest;
use App\Repositories\BlockUsersRepository;
use App\Utils\Transformer;

class BlockUsersController extends Controller
{
        /**
     * ProfileController constructor.
     *
     */
    private $_transformer;

    private $blockUsersRep;


    public function __construct(Transformer $transformer, BlockUsersRepository $blockUsersRep)
    {
        $this->_transformer = $transformer;
        $this->blockUsersRep = $blockUsersRep;
    }

    /**
     * 
     */
    public function blockUser(BlockUsersRequest $request) 
    {
        $user = $request->user();
        $block_user_id = $request->input('block_user_id');

        $blocked_users = $this->blockUsersRep->addBlockUser($user->id, $block_user_id);

        return $this->_transformer->success(['blocked_users' => $blocked_users]);
    }

    /**
     * 
     */
    public function unblockUser(BlockUsersRequest $request) 
    {
        $user = $request->user();
        $block_user_id = $request->input('block_user_id');

        $blocked_users = $this->blockUsersRep->removeBlockUser($user->id, $block_user_id);

        return $this->_transformer->success(['blocked_users' => $blocked_users]);
    }

}