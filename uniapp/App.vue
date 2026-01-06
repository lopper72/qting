<script>
	import {getUser} from "@/js_sdk/user.js"
	import {getLanchAd} from "@/js_sdk/index.js"
	import NativeUI from "viewapi/NativeUI.js"
	
	export default {
		onLaunch: function() {
			
			
			console.log('App Launch')
			// 保持屏幕常亮
			// #ifdef APP-PLUS
			uni.setKeepScreenOn({
			    keepScreenOn: true
			})
			// #endif
			//获取用户信息
			getUser().then(res=>{
				this.$store.dispatch("userInfo",res.data)
			}).catch(error=>{
				console.log(error)
			})
			//强制竖屏，禁止横屏
			// #ifdef APP-PLUS
			plus.screen.lockOrientation('portrait-primary')
			// #endif
			const info = uni.getSystemInfoSync()
			
			//添加状态栏高度
			this.$store.dispatch("statusBarHeight",info.statusBarHeight)
			//添加窗口宽高
			this.$store.dispatch("pageInfo",{
				width:info.windowWidth,
				height:info.windowHeight
			})
			
			var view = new NativeUI.Swiper("launch_swiper",{current:1,repeat:false,bounce:false})
			
			var adlist = []
			try{
				adlist = JSON.parse(uni.getStorageSync("ad_list"))
			}catch(e){
				console.log(e)
			}

			view.addImages(adlist && adlist.length ? adlist : [{
				src:"static/images/1080x1920.png"
			}])
			view.show()
			view.addEventListener("click",({current})=>{
				if(!current['url']) return
				if(current.url.indexOf("http") == 0){
					// #ifdef APP-PLUS
					plus.runtime.openURL(current.url)
					// #endif
					// #ifndef APP-PLUS
					window.open(current.url)
					// #endif
					return
				}
				if(current.url.indexOf("/pages/") == 0){
					clearInterval(timer)
					view.hide()
					uni.navigateTo({
						url:current.url
					})
					return
				}
			})
			
			let view2 = new NativeUI.View("daojishi");
			view2.style.width = "120rpx"
			view2.style.height = "50rpx"
			view2.style.right = "40rpx"
			view2.style.top = (info.statusBarHeight + 10) + "px"
			view2.style.backgroundColor = "rgba(0,0,0,0.7)"
			view2.style.color = "#FFFFFF"
			view2.style.borderRadius = "10rpx"
			view2.innerText = "正在启动"
			view2.style.textAlign = "center"
			view2.style.textValign = "middle"
			
			view.appendChild(view2)
			view2.show()
			let count = 5
			let timer = setInterval(()=>{
				count--
				if(count<=0){
					clearInterval(timer)
					count = 0
				}
				//view2.innerText = "启动 " + count +"S"
				if(count == 0){
					view.close()
					//view.fadeOut("fast")
				}
			},1000)
			//显示启动广告，显示后把默认启动图关闭
			// #ifdef APP-PLUS
			plus.navigator.closeSplashscreen()
			// #endif
			
			getLanchAd().then(res=>{
				var list = res.data
				if(!list) return
				var adlist = []
				list.forEach(item=>{
					if(!item.img_url) return;
					var length = item.img_url.indexOf("?")
					if(length != -1) item.img_url = item.img_url.substr(0,length)
					
					uni.downloadFile({
						url:item.img_url,
						success:(file) => {
							uni.saveFile({
							  tempFilePath: file.tempFilePath,
							  success: function (save) {
								adlist.push({
									src:save.savedFilePath,
									url:item.ad_url
								})
								if(adlist.length == list.length){
									uni.setStorageSync("ad_list",JSON.stringify(adlist))
								}
							  }
							});
						}
					})
				})
			}).catch(error=>{
				console.log(error)
			})
			
			let domModule = weex.requireModule('dom')
			domModule.addRule('fontFace', {
			  'fontFamily': "iconfont2",
			  src: "url('https://at.alicdn.com/t/font_1955254_47l6dnusbf.ttf')"
			})
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style lang="scss">
	/*每个页面公共css */
	.blank-height{
		height: 20rpx;
		background-color: #000000;
	}
	/*每个页面公共css */
	.icon-font {
	  font-family:iconfont2;
	  font-size: 16px;
	}
	.button-hover{
		opacity: 0.7;
	}
	.form-item-box{
		padding: 20rpx;
	}
	.form-input{
		font-size: $uni-font-size-base;
		color: $uni-text-color;
		background-color: #3B4144;
		padding:0 20rpx;
		height: 68rpx;
		border-radius: 10rpx;
	}
	.submit-button{
		border-radius: 10rpx;
		padding: 20rpx;
		background-color: #DD524D;
	}
	.submit-button-text{
		font-size: $uni-font-size-base;
		color: #F1F1F1;
		text-align: center;
	}
</style>
