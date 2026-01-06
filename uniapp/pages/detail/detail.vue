<template>
	<view class="content">
		<video id="myVideo" 
			style="width: 0px;height: 0px;"
			:autoplay="true"
			:title="videoTitle"
			:src="videoUrl"
			@error="videoErrorCallback" 
			:danmu-list="danmuList"
			@fullscreenchange="onFullscreen"
			@ended="onEnd"
			enable-danmu danmu-btn controls></video>
		<view class="top-box" v-if="videoInfo">
			<view class="bg-box">
				<image class="top-bg-image" :src="videoInfo.thumb" v-if="videoInfo.thumb"></image>
				<image class="top-bg-image" src="@/static/images/no_image.jpg" v-else></image>
			</view>
			<view class="top-conent-box">
				<view class="info-box">
					<view class="thumb-box">
						<image class="thumb-image" :src="videoInfo.thumb" v-if="videoInfo.thumb"></image>
						<image class="thumb-image" src="@/static/images/no_image.jpg" v-else></image>
						<text class="score-text">{{videoInfo.score}}</text>
					</view>
					<view class="info">
						<text class="year">{{videoInfo.year}}</text>
						<text class="country">{{videoInfo.region_str}}</text>
						<text class="duration">{{videoInfo.duration}}</text>
					</view>
					
				</view>
				
				<view class="play-btn" hover-class="btn-press" @click="playVideo(-1)">
					<text class="icon-font play-btn-text" style="margin-right: 10rpx;">&#xe672;</text>
					<text class="play-btn-text">播放</text>
				</view>
				<view class="subtitle-box">
					<text class="subtitle-text">{{videoInfo.subtitle}}</text>
				</view>
				<view class="tool-box">
					<view class="tool-item" @click="onAddcollect(videoInfo)">
						<text class="icon-font icon" v-if="videoInfo.is_collect">&#xe607;</text>
						<text class="icon-font icon" v-else>&#xe651;</text>
						<text class="text">片单</text>
					</view>
					<view class="tool-item" @click="onAddLike(videoInfo)">
						<text class="icon-font icon" v-if="videoInfo.is_like">&#xe60c;</text>
						<text class="icon-font icon" v-else>&#xe756;</text>
						<text class="text">点赞</text>
					</view>
					<view class="tool-item" @click="onShare">
						<text class="icon-font icon">&#xe600;</text>
						<text class="text">分享</text>
					</view>
					<view class="tool-item" @click="onDownload">
						<text class="icon-font icon">&#xe625;</text>
						<text class="text">下载</text>
					</view>
				</view>
			</view>
		</view>
		<view class="video-list-box">
			<view class="title">选择剧集</view>
			<view class="list-box">
				<text class="text" 
					:class="{'select':playIndex == index}" 
					v-for="(item,index) in videoList" 
					:key="item.id"
					@click="playVideo(index)">{{item.title}}</text>
			</view>
		</view>
		<view class="comment-box">
			<view class="title">影评</view>
			<view class="comment-list">
				<text v-if="!commentData || commentData.list.length == 0">暂无内容</text>
				<view v-else class="comment-item" v-for="(comment,index) in commentData.list" :key="comment.id">
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
					<view class="comment-content">{{comment.content}}</view>
					<view class="comment-image-list" v-if="comment.images">
						<image :src="image" class="comment-image" v-for="(image,_index) in comment.images" @click="onPreimages(comment.images,_index)"></image>
					</view>
					<view class="c-tool-box">
						<view class="c-tool-item" @click="onLike(comment)">
							<text class="icon-font c-tool-btn" v-if="comment.is_like">&#xe60c;</text>
							<text class="icon-font c-tool-btn" v-else>&#xe628;</text>
							<text class="c-tool-text">{{comment.like_num}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view style="height: 120rpx;"></view>
		<view class="add-comment" v-if="userInfo" @click="goToAddcomment">
			<image mode="aspectFill" :src="userInfo.avatar" class="avatar-image"></image>
			<text class="add-comment-text">我来发个影评~</text>
		</view>
		<view class="add-comment" v-else @click="goToLogin()">
			<text class="add-comment-text">登陆后可发表影评~</text>
		</view>
	</view>
</template>

<script>
	import {
		mapGetters
	} from 'vuex'
	import {
		getVideo,
		getVideoList,
		addCollect,
		removeCollect,
		addLike,
		removeLike,
		getComment
	} from "@/js_sdk/video.js"
	import {
		followUser,
		cancelFollowUser
	} from "@/js_sdk/user.js"
	import {transformImageUrlsInObject, transformImageUrlsInArray} from '@/utils/imageUtils.js'
	export default {
		data() {
			return {
				vid:null,
				videoInfo:null,
				videoList:[],
				playIndex:-1,
				videoUrl:null,
				danmuList:[],
				player:null,
				videoTitle:null,
				tabList:[],
				commentData:null
			}
		},
		computed:{
			...mapGetters(["userInfo","video"])
		},
		onLoad(options) {
			this.vid = options.id
			this.loadVideoList()
			this.getCommentList()
		},
		onShow() {
			if(this.video){
				this.videoInfo = this.video
				uni.setNavigationBarTitle({
					title:this.video.title
				})
			}
			this.loadViedoInfo()
		},
		onReachBottom() {
			if(this.commentData && this.commentData.current_page>=this.commentData.total_page) return
			this.commentData.current_page++
			
			this.getCommentData(getCommentList)
		},
		methods: {
			loadViedoInfo(){
				getVideo(this.vid).then(res=>{//console.log(res)
					// Transform image URLs in video info
					this.videoInfo = res.data
					transformImageUrlsInObject(this.videoInfo)
					this.videoTitle = this.videoInfo.title
					this.$store.dispatch("video",this.videoInfo)
					this.initPlayIndex()
					uni.setNavigationBarTitle({
						title:this.videoTitle
					})
				}).catch(error=>{
					console.log(error)
				})
			},
			initPlayIndex(){
				var movie = this.videoInfo
				if(movie && movie.history && this.videoList){
					this.videoList.forEach((item,index)=>{
						if(movie.history.movie_detail_id == item.id){
							this.playIndex = index
						}
					})
				}
			},
			loadVideoList(){
				getVideoList(this.vid).then(res=>{
					this.videoList = res.data.list
					if(!this.videoList) return
					this.tabList = []
					this.videoList.forEach(item=>{
						this.tabList.push(item.title)
					})
					this.initPlayIndex()
				}).catch(error=>{
					console.log(error)
				})
			},
			playVideo(index){
				if(index == -1){
					index = this.playIndex >=0 ? this.playIndex : 0
				}
				if(this.videoList.length == 0){
					uni.showToast({
						icon:"none",
						title:"暂无可播放视频",
						duration:2500
					})
					return
				}
				this.playIndex = index
				this.$store.dispatch("playlist",this.videoList)
				uni.navigateTo({
					animationType: 'none',
					url:"/pages/player/player?index="+index
				})
				
				/*if(index == this.playIndex && this.player){
					this.player.requestFullScreen()
					this.player.play()
					return 
				}
				this.playIndex = index
				if(!this.player){
					this.player = uni.createVideoContext('myVideo')
				}
				this.videoUrl = this.videoList[index].url
				this.player.requestFullScreen()
				this.player.play()*/
			},
			videoErrorCallback(e){
				//console.log(e)
			},
			onFullscreen({detail}){
				if(!detail.fullScreen && this.player){
					this.player.pause()
				}
			},
			onEnd(){
				if(this.playIndex<this.videoList.length - 1){
					uni.showToast({
						icon:"none",
						title:"正在自动切换下一集"
					})
					this.playIndex++
					this.videoUrl = this.videoList[this.playIndex].url
					this.player.play()
				}
			},
			goToLogin(){
				uni.navigateTo({
					url:"/pages/login/login"
				})
			},
			onAddcollect(info){
				if(!this.userInfo){
					this.goToLogin()
					return
				}
				if(info.is_collect){
					removeCollect({
						vid:info.id,
						type:4
					}).then(res=>{
						info.is_collect = 0
						uni.showToast({
							title:res.msg
						})
					}).catch(error=>{
						console.log(error)
					})
				}else{
					addCollect({
						vid:info.id,
						type:4
					}).then(res=>{
						info.is_collect = 1
						uni.showToast({
							title:res.msg
						})
					}).catch(error=>{
						console.log(error)
					})
				}
				
			},
			onAddLike(info){
				if(!this.userInfo){
					this.goToLogin()
					return
				}
				if(info.is_like){
					removeLike({
						vid:info.id,
						type:4
					}).then(res=>{
						info.is_like = 0
						uni.showToast({
							title:res.msg
						})
					}).catch(error=>{
						console.log(error)
					})
				}else{
					addLike({
						vid:info.id,
						type:4
					}).then(res=>{
						info.is_like = 1
						uni.showToast({
							title:res.msg
						})
					}).catch(error=>{
						console.log(error)
					})
				}
				
			},
			onShare(){
				uni.showToast({
					icon:"none",
					title:"此功能暂未开放"
				})
			},
			onDownload(){
				uni.showActionSheet({
				    itemList: this.tabList,
				    success:  res =>{
				        var video = this.videoList[res.tapIndex]
						if(!video || !video.url){
							uni.showToast({
								icon:"none",
								title:"没有可下载的视频文件"
							})
							return
						}
						const downloadTask = uni.downloadFile({
						    url: video.url,
						    success: (res) => {
								//console.log(res)
								uni.hideLoading()
						        if (res.statusCode === 200) {
									uni.saveImageToPhotosAlbum({
									  filePath: res.tempFilePath,
									  success: (_res) => {
										console.log(_res)
									  }
									});
						            uni.showToast({
						            	title:"视频下载成功"
						            })
						        }else{
									uni.showToast({
										icon:"none",
										title:"视频下载失败"
									})
								}
						    },
							fail:res=>{
								uni.hideLoading()
								uni.showToast({
									icon:"none",
									title:"视频下载出错"
								})
							}
						});
						
						downloadTask.onProgressUpdate((res) => {
							//console.log(res)
							uni.hideLoading()
							if(res.progress>=100) return
							uni.showLoading({
								mask:true,
								title:"正在下载" + res.progress + "%"
							})
						    //console.log('下载进度' + res.progress);
						    //console.log('已经下载的数据长度' + res.totalBytesWritten);
						    //console.log('预期需要下载的数据总长度' + res.totalBytesExpectedToWrite);
						});
				    },
				    fail: res => {
				        console.log(res.errMsg);
				    }
				});
			},
			goToAddcomment(){
				uni.navigateTo({
					url:"/pages/addComment/addComment?vid="+this.vid,
					animationType:"slide-in-bottom"
				})
			},
			getCommentList(replace){
				getComment({
					type:3,
					oid:this.vid,
					page:this.commentData ? this.commentData.current_page : 1
				}).then(res=>{
					if(replace){
						// Transform image URLs in comments
						this.commentData = transformImageUrlsInObject(res.data)
						// Also transform individual comments in the list
						if (this.commentData.list && this.commentData.list.length) {
							this.commentData.list = transformImageUrlsInArray(this.commentData.list)
						}
					}else{
						if(!this.commentData){
							// Transform image URLs in comments
							this.commentData = transformImageUrlsInObject(res.data)
							// Also transform individual comments in the list
							if (this.commentData.list && this.commentData.list.length) {
								this.commentData.list = transformImageUrlsInArray(this.commentData.list)
							}
						}else{
							// Transform new comments before adding to existing list
							const newComments = transformImageUrlsInArray(res.data.list)
							this.commentData.list = this.commentData.list.concat(newComments)
						}
					}

					//console.log(res)
				}).catch(error=>{
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
			}
		}
	}
</script>

<style lang="scss" scoped>
@import "../../common/font.css";

.content{
	display: flex;
	flex-direction: column;
	flex: 1;
	min-height: 100vh;
	background-color:#000000;
}
.top-box{
	display: flex;
	position: relative;
	flex-direction: column;
}
.bg-box{
	position: absolute;
	width: 100%;
	height: 100%;
}
.top-bg-image{
	filter:blur(8px);
	width: 100%;
	height: 100%;
}
.top-conent-box{
	z-index: 999;
	background-image: linear-gradient(rgba(28,28,28,0), rgba(28,28,28,1));
}
.thumb-box{
	width: 220rpx;
	height: 280rpx;
	background-color: #C8C7CC;
	margin: auto;
	margin-top: 60rpx;
	overflow:hidden;
	position: relative;
}
.thumb-image{
	width: 220rpx;
	height: 280rpx;
}
.score-text{
	position: absolute;
	font-size: $uni-font-size-base;
	color: #ce5e30;
	bottom: 5rpx;
	right: 10rpx;
}
.info{
	text-align: center;
	padding: 20rpx;
	color: #C0C0C0;
}
.info .year{
	margin-right: 20rpx;
}
.info .duration{
	margin-left: 20rpx;
}
.play-btn{
	display: flex;
	flex-direction: row;
	background-color:#f42c2c;
	padding: 20rpx;
	border-radius: 10rpx;
	flex-direction: row;
	justify-content: center;
	align-items: center;
	margin: 20rpx;
}
.play-btn-text{
	font-size: $uni-font-size-base;
	color: #FFFFFF;
}
.subtitle-box{
	padding: 20rpx;
	font-size: $uni-font-size-base;
	color: #FFFFFF;
	font-weight: bold;
}
.tool-box{
	display: flex;
	flex-direction: row;
	padding: 30rpx 80rpx 80rpx;
	justify-content: space-between;
	align-items: center;
}
.tool-box .tool-item{
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
}
.tool-box .tool-item .icon{
	color: #FFFFFF;
	font-size: 48rpx;
	margin-bottom: 20rpx;
}
.tool-box .tool-item .text{
	color: #C8C7CC;
	font-size: 28rpx;
}	
.video-list-box{
	background-color: #1c1c1c;
	padding: 20rpx;
}
.video-list-box .title{
	color: #FFFFFF;
	font-size: $uni-font-size-lg;
	font-weight: bold;
}
.video-list-box .list-box{
	padding: 20rpx 0rpx;
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}
.video-list-box .list-box .text{
	color: #F1F1F1;
	font-size: $uni-font-size-base;
	border: #F8F8F8 1px solid;
	padding: 10rpx 20rpx;
	margin: 10rpx;
}
.video-list-box .list-box .select{
	color:#F0AD4E;
	border: #F0AD4E 1px solid;
}
.btn-press{
	opacity: 0.7;
}
.comment-box{
	margin-top: 20rpx;
	background-color: #1c1c1c;
	padding: 20rpx;
}
.comment-box .title{
	color: #FFFFFF;
	font-size: $uni-font-size-lg;
	font-weight: bold;
}
.comment-box .comment-list{
	padding: 20rpx 0;
	color: #F8F8F8;
}
.add-comment{
	position: fixed;
	display: flex;
	flex-direction: row;
	background-color: #2e2e2f;
	width: 750rpx;
	height: 100rpx;
	bottom: 0rpx;
	justify-content: center;
	align-items: center;
}
.avatar-image{
	width: 60rpx;
	height: 60rpx;
	border-radius: 60rpx;
	border-width: 2px;
	border-style: solid;
	border-color: #00a3ff;
}
.add-comment-text{
	color: #C8C7CC;
	font-size: $uni-font-size-base;
	margin-left: 30rpx;
}
.comment-item{
	border-bottom: #353535 1px solid;
	padding: 20rpx 0;
}
.comment-item .info-box{
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	align-items: center;
}
.avatar-box{
	display: flex;
	flex-direction: row;
	align-items: center;
}
.avatar-box .avatar-image{
	width: 80rpx;
	height: 80rpx;
	border-radius: 80rpx;
	border-color: #ff7ddd;
}
.user-info{
	display: flex;
	flex-direction: column;
	margin-left: 20rpx;
}
.user-info .username{
	font-size: 32rpx;
	font-weight: bold;
}
.user-info .comment-date{
	font-size: 28rpx;
	color: #C8C7CC;
}
.comment-content{
	padding: 20rpx 0;
}
.comment-image-list{
	
}
.comment-image-list .comment-image{
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
.c-tool-box{
	display: flex;
	flex-direction: row;
	justify-content:flex-end;
	align-items: center;
	margin-top: 20rpx;
}
.c-tool-item{
	display: flex;
	flex-direction: row;
	align-items: center;
}
.c-tool-btn{
	display: flex;
	font-size: 32rpx;
	color: #F1F1F1;
}
.c-tool-text{
	display: flex;
	font-size: 32rpx;
	color: #F1F1F1;
	margin-left: 10rpx;
}
</style>
