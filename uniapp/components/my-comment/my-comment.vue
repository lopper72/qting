<template>
	<view style="flex: 1;">
		<list style="flex: 1;" @loadmore="onLoadMore" :loadmoreoffset="100" ref="list">
			<refresh class="loading-box" @refresh="onPullLoading" @pullingdown="onPulling" :display="pull_loading ? 'show' : 'hide'">
				<loading-indicator></loading-indicator>
				<text class="load-text" v-if="pull_loading">正在加载...</text>
				<text class="load-text" v-else>下拉刷新</text>
			</refresh>
			<cell>
				<view class="comment-list">
					<text class="nodata-box" v-if="!commentData || commentData.list.length == 0">{{loaded ? '暂无内容' : '正在加载...'}}</text>
					<view v-else class="comment-item" v-for="(comment,index) in commentData.list" :key="'list_'+comment.id">
						<view class="info-box">
							<view class="avatar-box">
								<image mode="aspectFill" class="avatar-image" :src="comment.avatar"></image>
								<view class="user-info">
									<text class="username">{{comment.nickname ? comment.nickname : comment.username}}</text>
									<text class="comment-date">{{comment.mtime}}</text>
								</view>
							</view>
							<text class="follow-btn" @click="onFollow(comment)" v-if="comment.is_follow == 0">关注</text>
							<text class="cancel-follow-btn" @click="cancelFollowUser(comment)" v-else>取消关注</text>
						</view>
						
						<text class="comment-content">{{comment.content}}</text>
						<view class="comment-image-list" v-if="comment.images">
							<image :src="image" 
								class="comment-image" 
								v-for="(image,_index) in comment.images" 
								@click="onPreimages(comment.images,_index)" 
								:key="'image_'+_index"></image>
						</view>
						<my-card style="margin-top: 20rpx;" :info="comment.movie" v-if="comment.movie"></my-card>
						<view class="tool-box">
							<view class="tool-item" @click="onLike(comment)">
								<text class="icon-font tool-btn" v-if="comment.is_like">&#xe60c;</text>
								<text class="icon-font tool-btn" v-else>&#xe628;</text>
								<text class="tool-text">{{comment.like_num}}</text>
							</view>
						</view>
					</view>
				</view>
			</cell>
		</list>
	</view>
</template>

<script>
	import {
		mapGetters
	} from 'vuex'
	import {
		getComment,
		addLike,
		removeLike
	} from "@/js_sdk/video.js"
	import {
		followUser,
		cancelFollowUser
	} from "@/js_sdk/user.js"
	import {transformImageUrlsInObject, transformImageUrlsInArray} from '@/utils/imageUtils.js'
	
	export default {
		data() {
			return {
				commentData:null,
				loaded:false,
				pull_loading:false
			};
		},
		props:{
			type:{
				type:Number,
				default:0
			},
			user_id:{
				type:Number,
				default:0
			},
			followed:{
				type:Boolean,
				default:false
			}
		},
		computed:{
			...mapGetters(["userInfo"])
		},
		mounted() {
			this.getCommentData()
		},
		methods:{
			getCommentData(replace){
				getComment({
					type:3,
					order:this.type,
					user_id:this.user_id,
					page:this.commentData ? this.commentData.current_page : 1
				},this.followed).then(res=>{//if(this.followed) console.log(res.data)
					this.loaded = true
					this.pull_loading = false
					if(replace){
						// Transform image URLs in comments
						this.commentData = res.data
						transformImageUrlsInObject(this.commentData)
						// Also transform individual comments in the list
						if (this.commentData.list && this.commentData.list.length) {
							transformImageUrlsInArray(this.commentData.list)
						}
					}else{
						if(!this.commentData){
							// Transform image URLs in comments
							this.commentData = res.data
							transformImageUrlsInObject(this.commentData)
							// Also transform individual comments in the list
							if (this.commentData.list && this.commentData.list.length) {
								transformImageUrlsInArray(this.commentData.list)
							}
						}else{
							// Transform new comments before adding to existing list
							transformImageUrlsInArray(res.data.list)
							this.commentData.list = this.commentData.list.concat(res.data.list)
							this.commentData.total_page = res.data.total_page
							this.commentData.current_page = res.data.current_page
						}
					}
					 this.$refs["list"].resetLoadmore();

					//console.log(res)
				}).catch(error=>{
					this.pull_loading = false
					this.loaded = true
					console.log(error)
				})
			},
			onPreimages(images,index){
				var list = []
				for(var i=0;i<images.length;i++){
					var item = images[i]
					if(!item) continue
					var length = item.indexOf("?")
					list.push(length == -1 ? item : item.substr(0,length))
				}
				
				uni.previewImage({
					current:index,
					urls:list
				})
			},
			onLoadMore(){
				if(this.commentData.current_page>=this.commentData.total_page) return
				this.commentData.current_page++
				
				this.getCommentData()
			},
			onFollow(item){
				if(!this.userInfo){
					this.goToLogin()
					return
				}
				uni.showLoading({
					title:"正在提交"
				})
				followUser({
					follow_id:item.user_id
				}).then(res=>{
					uni.showToast({
						title:res.msg
					})
					item.is_follow = 1
				}).catch(error=>{
					console.log(error)
				})
			},
			cancelFollowUser(item){
				if(!this.userInfo){
					this.goToLogin()
					return
				}
				uni.showLoading({
					title:"正在提交"
				})
				cancelFollowUser({
					follow_id:item.user_id
				}).then(res=>{
					uni.showToast({
						title:res.msg
					})
					item.is_follow = 0
				}).catch(error=>{
					console.log(error)
				})
			},
			onPulling(e){
				//console.log(e)
			},
			onPullLoading(){
				if(this.commentData) this.commentData.current_page = 1
				this.pull_loading = true
				this.getCommentData(true)
			},
			onLike(item){
				if(!this.userInfo){
					this.goToLogin()
					return
				}
				uni.showLoading({
					title:"正在提交"
				})
				if(item.is_like == 0){
					addLike({
						vid:item.id,
						type:2
					}).then(res=>{
						uni.showToast({
							title:res.msg
						})
						item.is_like = 1
						item.like_num++
					}).catch(error=>{
						console.log(error)
					})
				}else{
					removeLike({
						vid:item.id,
						type:2
					}).then(res=>{
						uni.showToast({
							title:res.msg
						})
						item.is_like = 0
						item.like_num--
					}).catch(error=>{
						console.log(error)
					})
				}
			},
			goToLogin(){
				uni.navigateTo({
					url:"/pages/login/login"
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
.loading-box{
	width: 750rpx;
	justify-content: center;
	align-items: center;
	flex-direction: row;
	padding: 20rpx;
}
.load-text{
	color: #F1F1F1;
	margin-left: 30rpx;
	font-size: $uni-font-size-base;
}
.nodata-box{
	padding: 20rpx;
	text-align: center;
	font-size: $uni-font-size-base;
	color: #F1F1F1;
}
.comment-list{
	padding: 20rpx;
}
.comment-item{
	border-bottom-color: #353535;
	border-bottom-width: 1px;
	padding: 20rpx 0;
}
.info-box{
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
}
.avatar-box{
	flex-direction: row;
	align-items: center;
}
.avatar-image{
	width: 80rpx;
	height: 80rpx;
	border-radius: 80rpx;
	border-width: 2px;
	border-color: #ff7ddd;
}
.user-info{
	flex-direction: column;
	margin-left: 20rpx;
}
.username{
	font-size: 32rpx;
	font-weight: bold;
	color: #F1F1F1;
}
.comment-date{
	font-size: 28rpx;
	color: #C8C7CC;
}
.comment-content{
	padding: 20rpx 0;
	font-size: $uni-font-size-base;
	color: #F1F1F1;
}
.comment-image-list{
	flex-direction: row;
	flex-wrap: wrap;
}
.comment-image{
	width: 215rpx;
	height: 215rpx;
	margin: 10rpx;
}
.follow-btn{
	background-color: #DD524D;
	color: #F1F1F1;
	font-size: $uni-font-size-sm;
	padding: 10rpx 20rpx;
	border-radius: 10rpx;
}
.cancel-follow-btn{
	background-color: #3F536E;
	color: #F1F1F1;
	font-size: $uni-font-size-sm;
	padding: 10rpx 20rpx;
	border-radius: 10rpx;
}
.tool-box{
	flex-direction: row;
	justify-content:flex-end;
	align-items: center;
	margin-top: 20rpx;
}
.tool-item{
	flex-direction: row;
	align-items: center;
}
.tool-btn{
	font-size: 32rpx;
	color: #F1F1F1;
}
.tool-text{
	font-size: 32rpx;
	color: #F1F1F1;
	margin-left: 10rpx;
}
</style>
