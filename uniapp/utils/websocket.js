let base_url = null;

if(process.env.NODE_ENV === 'development'){
    base_url = 'ws://192.168.31.9:5200';
}else{
	base_url = null;
}

const websocket = {
	isConnected:false,
	sendList:[],
	__heart_timer:null,
	start(){
		uni.connectSocket({
		  url: base_url
		});
		var _this = this;
		uni.onSocketOpen((res)=>{
			_this.isConnected = true;
			_this.__sendList();
			console.log('WebSocket连接已打开！');
			//消息订阅
			_this.send(null,['broadcast','message'],'subscribe');
			//心跳保活
			_this.__heart_timer = setTimeout(()=>{_this.__heart()},30000);
		});
		uni.onSocketError(function (res) {
			clearTimeout(_this.__heart_timer);
			_this.isConnected = false;
			console.log('WebSocket连接打开失败，请检查！');
			//连接失败，10秒后偿试重连
			setTimeout(()=>{
				if(_this.isConnected) return;
				console.log("正在重新连接");
				uni.connectSocket({
				  url: base_url
				});
			},10000);
		});
		uni.onSocketClose((res)=>{
			clearTimeout(_this.__heart_timer);
			_this.isConnected = false;
			console.log('WebSocket 已关闭！');
			//当断开连接，10秒后偿试重连
			setTimeout(()=>{
				if(_this.isConnected) return;
				console.log("正在重新连接");
				uni.connectSocket({
				  url: base_url
				});
			},10000);
		});
	},
	__sendList(){
		if(!this.isConnected){
			return;
		}
		this.sendList.map(params=>{
			this.send(params);
		});
		this.sendList = [];
	},
	send(data,action,todo){
		if(!this.isConnected){
			console.log("WebSocket未连接");
			this.sendList.push(data);
			return;
		}
		var token = uni.getStorageSync('c_a_t_0_01_3');
		var message = {
			token:token,
			action:action ? action : 'broadcast',
			todo:todo ? todo : null,
			message:data
		}
		uni.sendSocketMessage({
			data:JSON.stringify(message),
			fail() {
				uni.showToast({
					title:"消息发送失败"
				})
			}
		});
	},
	on(callback){
		uni.onSocketMessage(res=>{
			try{
				var res = JSON.parse(res.data);
				if(typeof callback == 'function') callback(res);
			}catch(e){
				if(typeof callback == 'function') callback(res.data);
			}
			
		});
	},
	__heart(){
		clearTimeout(this.__heart_timer);
		this.send(null,null,'heart');
		this.__heart_timer = setTimeout(()=>{this.__heart()},30000);
	},
	close(){
		uni.closeSocket()
	}
}

export default websocket;