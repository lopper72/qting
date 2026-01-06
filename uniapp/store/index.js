import Vue from "vue"
import Vuex from "vuex"
import getters from './getters'

Vue.use(Vuex);

const store = new Vuex.Store({
	getters,
	state: {
		tempVideo:null,
		tempVideoList:null,
		iStatusBarHeight: 0,
		pageInfo: {
			width: 0,
			height: 0
		},
		userInfo: null
	},
	mutations: {
		setTempVideo(state,data){
			state.tempVideo = data
		},
		setTempVideoList(state,data){
			state.tempVideoList = data
		},
		setStatusBarHeight(state, data) {
			state.iStatusBarHeight = data;
		},
		setPageInfo(state, data) {
			state.pageInfo = data;
		},
		setUserInfo(state, data) {
			state.userInfo = data
		}
	},
	actions: {
		video(context, data){
			context.commit("setTempVideo", data)
		},
		playlist(context, data){
			context.commit("setTempVideoList", data)
		},
		statusBarHeight(context, data) {
			context.commit("setStatusBarHeight", data)
		},
		pageInfo(context, data) {
			context.commit("setPageInfo", data)
		},
		userInfo(context, data) {
			context.commit("setUserInfo", data)
		}
	}
})

export default store
