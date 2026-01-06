const getters = {
	video:state => state.tempVideo,
	playlist:state => state.tempVideoList,
	statusBarHeight:state => state.iStatusBarHeight,
	pageInfo:state => state.pageInfo,
	userInfo:state => state.userInfo,
	isLogin:state => {
		return state.userInfo ? true : false
	},
}
export default getters