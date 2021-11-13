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
                $input=App::get_ci()->input;
                return $this->response_success(['comment'=>Comment_model::preparation(Comment_model::create([
                   'user_id'=>User_model::get_session_id(),
                   'assign_id'=>(int) $input->post('postId'),
                   'likes'=>0,
                   'text'=>(string) $input->post('commentText'),
                   'reply_id'=>(int) $input->post('replyId'),
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
        if(!$comment->get_id())
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
        if(!$post->get_id())
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

        $this->load->library('form_validation');
        $validator=App::get_ci()->form_validation;
        $validator->set_rules('sum', 'Sum', 'required|numeric|min_length[1]')
            ->set_rules('email','Email','required|valid_email');
        if(!$validator->run())
            return $this->response_error($validator->error_string());

        $input=App::get_ci()->input;
        $sum = (float)$input->post('sum');

        // Не хватает данных пользователя
        $email = (string)$input->post('email');
        // Не хватает проверки источника данных по хосту, серетному ключу и шифрованию данных

        $user=User_model::find_user_by_email($email);
        if(!$user->get_id())
            return $this->response_error('User not found');

        if(!$user->add_money($sum))
            return  $this->response_error('Something went wrong');

        // -- Транзакция объединяет запросы для того чтобы выполнились либо оба,
        // либо не одного чтобы не было не согласованных изменений
        // -- Обнолвнеие значений полей пользвателя нужно выполенять через получение данных из того же запроса,
        // чтобы данные были точные: Если вместо этого запроса просто обновить данные полученые из увеличения
        // суммы полученных данных из SELECT запроса и после выполнить UPDATE то данные могут быть паралельно
        // обновлены другим потоком в перыве между SELECT и UPDATE  и это привет к тому что на балансе не
        // будут отображаться данные внесенные этим параллельным потоком
        // -- Лог операций в отдельной таблице необходим для возможности дальнейшей отмены операции


        // PS: Фраза "максимально безопасна и отказоустойчива" сбивет меня с толку.
        // Я не могу понять что под этим подразумевается. Возможно я не сталкивался с подобными проблемами,
        // обосновние, который вы ожидаете, либо это тривиально для меня. Я вижу только одно верное решение (которое
        // я реализовал). Единственное что приходит на ум это сменить MYsql на Postgres =)
        // Что тут обосновывать не прдеставляю. Ясно что не давая подробного описания, вы не хотите давать подскаку
        // к решению, однако, как я уже писал, вызываете дессонанс. Думаю правильнее задать этот вопрос во время
        // беседы после выполнения. И если я не дал того обоснования которого вы ожидаете, я был бы признателен, если бы
        // мне дали пояснения

        return $this->response(['success'=>true]);
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

        $this->load->library('form_validation');
        $validator=App::get_ci()->form_validation;
        $validator->set_rules('id', 'ID', 'required|integer|min_length[1]');
        $pack=Boosterpack_model::get_by_id((int)App::get_ci()->input->post('id'));
        if(!$pack->get_id())
            return  $this->response_error('Boosterpack not found');
        $user=User_model::get_user();
        if($pack->get_price()>$user->get_wallet_balance())
            return  $this->response_error('Insufficient funds');
        App::get_s()->start_trans();
        try {
            $amount=$pack->open();
            $user->remove_money($pack->get_price(),$amount,$pack->get_id());

        }
        catch (Exception $e)
        {
            App::get_s()->rollback();
            return $this->response_error($e->getMessage());
        }
        App::get_s()->commit();
        return  $this->response_success(compact('amount'));

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
