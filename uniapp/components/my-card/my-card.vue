<template>
	<view class="card-box" @click="goTodetail(info)">
		<view class="left-box">
			<text class="card-title">{{info.title}}</text>
			<text class="card-subtitle" v-if="info.type_str">类型：{{info.type_str}}</text>
			<text class="card-subtitle" v-if="info.category_str">分类：{{info.category_str}}</text>
			<text class="card-subtitle" v-if="info.region_str">地区：{{info.region_str}}</text>
			<text class="card-content" v-if="info.intro">{{info.intro}}</text>
		</view>
		<view class="right-box">
			<image class="cate-image" :src="info.thumb" v-if="info.thumb"></image>
			<image class="cate-image" v-else src="@/static/images/no_image.jpg"></image>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				
			}
		},
		props:{
			info:{
				type:Object,
				default:{}
			}
		},
		methods: {
			goTodetail(item){
				this.$store.dispatch("video",item)
				uni.navigateTo({
					url:"/pages/detail/detail?id="+item.id
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
.card-box{
	background-color: #313132;
	border-radius: 10rpx;
	padding: 10rpx 20rpx;
	flex-direction: row;
	justify-content: space-between;
}
.card-title{
	font-size: $uni-font-size-base;
	color: #C8C7CC;
	margin-bottom: 10rpx;
}
.card-subtitle{
	font-size: $uni-font-size-sm;
	color: #C8C7CC;
}
.cate-image{
	width: 120rpx;
	height: 160rpx;
	border-radius: 10rpx;
}
.card-content{
	font-size: $uni-font-size-sm;
	color: #C8C7CC;
	width: 500rpx;
	lines:3;
	text-overflow: ellipsis;
}
</style>
