<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Login_model;
use Model\Comment_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_user_auth()
    {
        $this->response_success(['is_logged'=>User_model::is_logged()]);
    }
    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function login()
    {
        // TODO: task 1, аутентификация
        $this->load->library('form_validation');
        $this->form_validation->set_rules('login', 'Login', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            $this->response_error($this->form_validation->error_string());
        }
        else
        {
            try {

                $user=Login_model::login();
                return $this->response_success(['user'=>[
                    'id'=>$user->get_id(),
                ]]);

            } catch (Exception $e) {

                $this->response_error($e->getMessage());
            }
        }
    }

    public function logout()
    {
        // TODO: task 1, аутентификация
        Login_model::logout();

        return $this->response(redirect('/'));
    }
    public function get_sub_comments(int $reply_id)
    {

        try {
            return $this->response_success(['comments'=>Comment_model::preparation_many(Comment_model::get_all_by_replay_id($reply_id))]);

        } catch (Exception $e) {

            $this->response_error($e->getMessage());
        }
    }
    public function comment()
    {
        // TODO: task 2, комментирование
        if(!User_model::is_logged())
            return $this->response_error('Authorization required');

        $this->load->library('form_validation');
        $validator=App::get_ci()->form_validation;
        $validator->set_rules('postId', 'Post ID', 'required|integer|min_length[1]')
            ->set_rules('commentText','Comment text','required')
            ->set_rules('replyId','Reply ID','integer|min_length[1]')
        ;
        if ($validator->run() == FALSE)
        {
            $this->response_error($validator->error_string());
        }
        else
        {
            try {

                return $this->response_success(['comment'=>Comment_model::preparation(Comment_model::create([
                    'user_id'=>User_model::get_session_id(),
                   'assign_id'=>$_POST['postId'],
                   'likes'=>0,
                   'text'=>$_POST['commentText'],
                   'reply_id'=>$_POST['replyId'],
                ]))]);
            } catch (Exception $e) {

                $this->response_error($e->getMessage());
            }
        }
    }

    public function like_comment(int $comment_id)
    {
        // TODO: task 3, лайк комментария

        $comment=Comment_model::find($comment_id);
        if(!$comment)
            return $this->response_error('Comment not found');

        $user=User_model::get_user();
        if($user->get_likes_balance()<=0)
            return $this->response_error('Likes balance is empty');
        App::get_s()->start_trans();
        if(!$comment->increment_likes($user))
        {
            App::get_s()->rollback();
            return  $this->response_error('Something going wrong,please try again');
        }
        App::get_s()->commit();
        $likes=$comment->get_likes_from_db();
        if(is_null($likes))
            return  $this->response_error('Likes not found');

        return  $this->response(compact('likes'));
    }

    public function like_post(int $post_id)
    {
        // TODO: task 3, лайк поста
        $post=Post_model::find($post_id);
        if(!$post)
            return $this->response_error('Post not found');

        $user=User_model::get_user();
        if($user->get_likes_balance()<=0)
            return $this->response_error('Likes balance is empty');
        App::get_s()->start_trans();
        if(!$post->increment_likes($user))
        {
            App::get_s()->rollback();
            return  $this->response_error('Something going wrong,please try again');
        }
        App::get_s()->commit();
        $likes=$post->get_likes_from_db();
        if(is_null($likes))
            return  $this->response_error('Likes not found');

        return  $this->response(compact('likes'));
    }

    public function add_money()
    {
        // TODO: task 4, пополнение баланса

        $sum = (float)App::get_ci()->input->post('sum');

    }

    public function get_post(int $post_id) {
        // TODO получения поста по id
        $post=Post_model::find_post_by_id($post_id);
        return $this->response_success(['post'=>Post_model::preparation($post,'full_info')]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        // TODO: task 5, покупка и открытие бустерпака
    }





    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }
}
