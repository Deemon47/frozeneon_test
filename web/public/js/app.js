const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';
Vue.component('comment', {
	props:{
		comment:Object,
		warnMessage:String,
	},
	template:'<div>' +
		'<p class="card-text" >\n' +
		'    {{comment.user.personaname + \' - \'}}\n' +
		'    <small class="text-muted">{{comment.text}}</small>\n' +
		'    <a role="button" @click="addLike">\n' +
		'        <svg class="bi bi-heart-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
		'            <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" clip-rule="evenodd"/>\n' +
		'        </svg>\n' +
		'        {{ comment.likes }}\n' +
		'    </a>\n' +
		'    <a role="button" @click="showForm=true">\n' +
		'      <svg id="Layer_1" width="1em" height="1em"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 511.971 511.971" style="enable-background:new 0 0 511.971 511.971;" xml:space="preserve"> <g> <g> <g> <path d="M444.771,235.493c-58.987-56.32-138.347-64-167.467-64.747V96.079c0-5.867-4.8-10.667-10.667-10.667 c-2.453,0-4.907,0.853-6.827,2.453L78.478,237.199c-4.587,3.733-5.227,10.453-1.493,15.04c0.427,0.533,0.96,0.96,1.493,1.493 l181.333,149.333c4.587,3.733,11.307,3.093,15.04-1.493c1.6-1.92,2.453-4.267,2.453-6.827v-77.44 c29.76-8.107,143.893-28.693,214.613,103.787c1.813,3.52,5.44,5.653,9.387,5.653c3.413,0,6.72-1.6,8.853-4.693 c1.28-1.813,1.813-4.053,1.813-6.293C511.865,338.639,489.251,278.053,444.771,235.493z M324.131,290.533 c-35.52,0-60.48,8.533-61.12,8.853c-4.267,1.493-7.04,5.547-7.04,10.027v62.72l-153.92-126.72l153.92-126.72v62.72 c0,2.88,1.173,5.653,3.307,7.68c2.133,2.027,4.907,3.093,7.893,2.987c0.96,0,97.813-3.52,163.093,58.987 c32.107,30.72,51.52,72.32,58.027,124.16C436.665,305.679,371.171,290.533,324.131,290.533z"/> <path d="M199.331,387.066c-0.213-0.107-0.32-0.32-0.533-0.427L27.385,245.413l171.413-141.12 c4.693-3.627,5.547-10.347,1.92-14.933c-3.627-4.587-10.347-5.547-14.933-1.92c-0.213,0.107-0.32,0.32-0.533,0.427L3.918,237.199 c-4.587,3.733-5.227,10.453-1.493,15.04c0.427,0.533,0.96,0.96,1.493,1.493l181.333,149.333c4.48,3.84,11.2,3.413,15.04-0.96 C204.131,397.626,203.705,390.906,199.331,387.066z"/> </g> </g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> <g> </g> </svg>\n' +
		'    </a>\n' +
		'<div class="alert alert-warning" v-if="showForm && warnMessage">{{warnMessage}}</div>'+
		'<form class="form-inline" v-if="showForm">\n' +
		'  <div class="form-group">\n' +
		'    <input type="text" class="form-control"  v-model="commentText">\n' +
		'  </div>\n' +
		'  <button type="button" class="btn btn-primary" :disabled="warnMessage || !commentText" @click="reply">Reply to comment</button>\n' +
		'</form>'+
		'</p>' +
		'<div class="row" v-if="subComments.length">' +
		'	<div class=".col-1">' +
		'		<svg width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve"> <metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata> <g><g><g><path d="M62.5,115.3c0-10.5,0-22.8-3.5-33.3c-1.8-10.5-21-10.5-22.8,0c-1.8,10.5-1.8,21-3.5,31.5c0,7,0,12.3,0,19.3c0,3.5,0,7,1.7,10.5c1.8,1.8,3.5,3.5,5.3,5.3c-3.5-1.7-3.5-3.5-1.7,0c3.5,7,14,7,15.8,0v-1.7l0,0c3.5-3.5,3.5-7,3.5-12.3C62.5,129.3,62.5,122.3,62.5,115.3z"/><path d="M32.8,258.9c0-5.3-7-5.3-8.8,0c-1.7,31.5-10.5,63-10.5,94.5c0,19.3,29.8,19.3,29.8,0C43.3,321.9,36.3,290.4,32.8,258.9z"/><path d="M45,460.1c1.8-7-8.8-14-14-5.3c-7,15.8-12.3,31.5-14,49c-1.8,15.8-5.3,31.5,1.7,45.5c3.5,7,14,7,17.5,0c7-10.5,5.3-22.8,5.3-35C41.5,495.1,43.3,477.6,45,460.1z"/><path d="M41.5,699.9c-7-17.5-12.3-33.3-14-50.8c-1.8-10.5-17.5-10.5-17.5,0c0,15.8,0,31.5,1.8,45.5c1.8,15.8,5.3,36.8,17.5,47.3c5.3,5.3,17.5,3.5,19.3-5.3C52,722.7,46.8,712.2,41.5,699.9z"/><path d="M148.3,831.2c-8.8-7-22.8-5.3-33.3-8.8c-14-3.5-24.5-12.3-29.8-26.3c-3.5-8.8-17.5-1.8-14,5.3c5.3,17.5,15.8,29.8,29.8,38.5c14,7,35,15.8,49,5.3C153.5,841.7,153.5,834.7,148.3,831.2z"/><path d="M321.5,850.4c-21-19.3-56-14-82.3-12.3c-12.3,0-12.3,17.5,0,19.3c12.3,0,24.5,1.7,36.8,3.5c12.3,1.8,26.3,7,38.5,5.3C321.5,866.2,328.5,857.4,321.5,850.4z"/><path d="M510.6,846.9c-29.8-17.5-71.8-12.3-103.3,0c-12.3,5.3-7,22.8,5.3,19.2c31.5-7,61.3,0,92.8,1.8C517.6,869.7,521.1,852.2,510.6,846.9z"/><path d="M687.3,838.2c-28-8.8-59.5-3.5-87.5-1.7c-14,0-14,21,0,21c28,1.7,61.3,7,87.5-1.8C697.8,853.9,697.8,841.7,687.3,838.2z"/><path d="M853.6,831.2c-12.3-12.3-28-7-43.8-5.3c-17.5,1.8-35,5.3-52.5,10.5c-12.3,5.3-8.8,24.5,5.3,21c17.5-5.3,35-7,52.5-7c14,0,26.3,3.5,36.8-7C857.1,841.7,857.1,834.7,853.6,831.2z"/><path d="M976.1,818.9c-40.3-10.5-68.3-43.8-106.8-59.5c-14-5.3-21,15.8-10.5,24.5c26.3,17.5,49,42,77,59.5c-10.5,8.8-21,17.5-33.3,26.3c-12.2,8.8-31.5,12.2-40.3,24.5c-5.3,7-7,17.5,0,24.5c33.3,29.8,99.8-42,120.8-64.8C995.4,841.7,990.1,822.4,976.1,818.9z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></g> </svg>'+
		'	</div>'+
		'	<div class=".col-11">' +

		'	<div class="ml-3" v-for="sub in subComments">' +

		'		<comment :warnMessage="warnMessage" :comment="sub" @like="addLikeTransit" @reply="replyTransit"></comment>' +
		'	</div>'+

		'</div>'+
		'</div>'+
		'</div>',
	emit:[
		'like',
		'reply',
	],
	data: function () {
		return {
			showForm:false,
			commentText: '',
			subComments:[],
		}
	},
	created() {
		this.reloadComments();
	},
	methods: {
		reloadComments(){
			var self=this;
			axios
				.get('/main_page/get_sub_comments/'+this.comment.id,)
				.then(function (response) {
					self.subComments = response.data.comments;
				})
		},
		addLike:function ()
		{
			var self=this;
			this.$emit('like', {
				id:this.comment.id,
				callback:function (likes){
					self.comment.likes=likes;
			}});
		},
		reply() {
			if(!this.commentText)
				return ;
			this.showForm=false;
			self=this;
			this.$emit('reply', {
				text:this.commentText,
				reply_id:this.comment.id,
				callback:function (){
					self.reloadComments();
				}
			});
			this.commentText='';
		},
		addLikeTransit:function (data)
		{
			this.$emit('like',data);
		},
		replyTransit:function (data)
		{
			this.$emit('reply',data);
		},

	}
})
var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidSum: false,
		is_logged:false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		boosterpacks: [],
		errorMessage:null,
		bpErrorMessage:null,
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		},
		warnMessage:function (){
			return !this.is_logged?'For add comment authorization required':null;
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})

		axios
			.get('/main_page/get_user_auth')
			.then(function (response) {
				self.is_logged = response.data.is_logged;
			})
		axios
			.get('/main_page/get_boosterpacks')
			.then(function (response) {
				self.boosterpacks = response.data.boosterpacks;
			})
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false

				form = new FormData();
				form.append("login", self.login);
				form.append("password", self.pass);

				axios.post('/main_page/login', form)
					.then(function (response) {
						if('error_message' in response.data)
						{
							self.errorMessage=response.data.error_message;
							return;
						}
						self.errorMessage=null;
						if(response.data.user) {
							location.reload();

						}
						setTimeout(function () {
							$('#loginModal').modal('hide');
						}, 500);


					})
			}
		},
		addComment: function(id,reply_id) {
			var self = this;
			if(self.commentText) {
				this.sendAddCommentRequest(self.commentText)
			}

		},
		sendAddCommentRequest:function (comment_text,reply_id,callback)
		{
			var comment = new FormData();
			comment.append('postId', this.post.id);
			comment.append('commentText', comment_text);
			if(reply_id)
				comment.append('replyId', reply_id);
			var self=this;
			axios.post(
				'/main_page/comment',
				comment
			).then(function (resp) {
				if(!reply_id)
				{
					self.post.coments.push(resp.data.comment);
					this.commentText='';
				}
				if(callback)
					callback();
			});
		},
		refill: function () {
			var self= this;
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				sum = new FormData();
				sum.append('sum', self.addSum);
				axios.post('/main_page/add_money', sum)
					.then(function (response) {
						setTimeout(function () {
							$('#addModal').modal('hide');
						}, 500);
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (type, id,callback) {
			var self = this;
			const url = '/main_page/like_' + type + '/' + id;
			axios
				.get(url)
				.then(function (response) {
					if(type=='post')
						self.likes = response.data.likes;
					if(callback)
						callback(response.data.likes);
				})

		},
		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);
			this.bpErrorMessage=null;
			axios.post('/main_page/buy_boosterpack', pack)
				.then(function (response) {
					self.amount = response.data.amount
					if(response.data.error_message)
						self.bpErrorMessage=response.data.error_message;
					if(self.amount !== 0){
						setTimeout(function () {
							$('#amountModal').modal('show');
						}, 500);
					}
				})
		},
		addLikeToComment:function (data)
		{
			this.addLike('comment',data.id,data.callback);
		},
		replyToComment:function (comment)
		{
			this.sendAddCommentRequest(comment.text,comment.reply_id,comment.callback);
		},
	}
});

