<?php
namespace Model;

use Model\User_model;
use App;
trait IncrementLikesTrait
{

    /**
     * @param User_model $user
     *
     * @return bool
     * @throws Exception
     */

    public  function increment_likes(User_model $user): bool
    {
        // TODO: task 3, лайк комментария
        if(!$user->decrement_likes())
            return false;

        App::get_s()->from($this->get_table())
            ->where(['id'=>$this->get_id()])
            ->update('likes = likes +1')
            ->execute();

        return   App::get_s()->is_affected();
    }
    public function get_likes_from_db() :?int
    {
        $res=App::get_s()->from($this->get_table())
            ->where('id',$this->get_id())
            ->select('likes')
            ->one();
        return $res ?$res['likes'] :null;
    }
}
