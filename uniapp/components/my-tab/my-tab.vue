<template>
	<view ref="tabBox">
		<scroll-view :style="{height:height}" :scroll-with-animation="true" :scroll-x="true" :show-scrollbar="false" class="tab-box" @scroll="_onScroll" :scroll-left="scrollTo">
			<view ref="scrollView" class="tab-box">
				<view class="tab-item" v-for="item,index in list" :key="index" :ref="'tab_'+index" @click="_onClick(index)">
					<text :style="{fontSize:tabIndex == index ? activedSize : fontSize,color:tabIndex == index ? activedColor : color,fontWeight:tabIndex == index ? activedWeight : null}">{{item.title}}</text>
				</view>
			</view>
			<view 
				v-if="showUnderline" 
				class="underline" 
				:style="{left:underlineLeft - boxLeft,width:underLineWidth,backgroundColor:underLineColor,bottom:underLineBottom,height:underLineHeight,borderRadius:underLineHeight}"></view>
		</scroll-view>
	</view>
</template>

<script>
	const dom = uni.requireNativePlugin('dom');
	export default {
		data() {
			return {
				boxLeft:0,
				boxWidth:0,
				underlineLeft:0,
				underLineWidth:0,
				scrollLeft:0,
				tabIndex:0,
				scrollTo:0,
				scrollWidth:0
			};
		},
		props:{
			current:{
				type:Number,
				default:0
			},
			list:{
				type:Object,
				default:[]
			},
			showUnderline:{
				type:Boolean,
				default:true
			},
			underLineColor:{
				type:String,
				default:"#F0AD4E"
			},
			underLineBottom:{
				type:Number,String,
				default:0
			},
			underLineHeight:{
				type:Number,String,
				default:"6rpx"
			},
			fontSize:{
				type:Number,String,
				default:"32rpx"
			},
			color:{
				type:String,
				default:"#000000"
			},
			activedColor:{
				type:String,
				default:"#000000"
			},
			activedSize:{
				type:Number,String,
				default:"36rpx"
			},
			activedWeight:{
				type:Number,String,
				default:"bold"
			},
			height:{
				type:Number,String,
				default:"50rpx"
			}
		},
		watch:{
			current(index){
				if(this.tabIndex != index) this._getItemInfo(index)
			},
			list(){
				this._getItemInfo(this.tabIndex)
			}
		},
		mounted() {
			this.$nextTick(()=>{
				setTimeout(()=>{
					var tabBox = this.$refs["tabBox"]
					dom.getComponentRect(tabBox,res=>{
						this.boxLeft = res.size.left
						this.boxWidth = res.size.width
						this._scrollToCenter()
					})
				},50)
				this._getItemInfo(this.current)
			})
		},
		onResize() {
			var tabBox = this.$refs["tabBox"]
			dom.getComponentRect(tabBox,res=>{
				this.boxLeft = res.size.left
				this.boxWidth = res.size.width
				this._scrollToCenter()
			})
			this._getItemInfo(this.tabIndex)
		},
		methods:{
			_onClick(index){
				this._getItemInfo(index)
			},
			_onScroll({detail}){
				this.scrollLeft = detail.scrollLeft
			},
			_getItemInfo(index){
				if(this.list.length == 0) return
				this.tabIndex = index
				this.$emit("change",{index:index,detail:this.list[index]})
				setTimeout(()=>{
					var scrollView = this.$refs["scrollView"]
					dom.getComponentRect(scrollView,res=>{
						this.scrollWidth = res.size.width
						this._scrollToCenter()
					})
					var item = this.$refs['tab_'+index][0]
					dom.getComponentRect(item,res=>{
						this.underlineLeft = res.size.left + this.scrollLeft
						this.underLineWidth = res.size.width
						this._scrollToCenter()
					})
				},50)
			},
			_scrollToCenter(){
				var center = this.boxWidth/2
				var scrollLeft = (this.underlineLeft + this.underLineWidth/2) - center
				var maxTo = this.scrollWidth - this.boxWidth
				
				if(scrollLeft<0) scrollLeft = 0
				if(maxTo>0 && scrollLeft>maxTo) scrollLeft = maxTo
				this.scrollTo = scrollLeft
			}
		}
	}
</script>

<style lang="scss" scoped>
	.tab-box{
		flex-direction: row;
		align-items: center;
	}
	.tab-item{
		margin-right: 20rpx;
	}
	.underline{
		position: absolute;
		width: 0;
		bottom:0rpx;
		transition-property:width,left;
		transition-duration: 0.3s;
	}
</style>
