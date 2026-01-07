import config from '@/common/config.js'


const request = {
	configs:{
		base_url:config.base_url
	},
	requestUrl:'',
	lastResult:{},
	isStopRquest:false,
	lastRquestTime:0,
	getBaseUrl(){
		return this.configs.base_url;
	},
	getRequestUrl(path){
		return this.getBaseUrl()+path;
	},
	getHeader(header,path,token,white){
		if(typeof header == 'undefined' || !header) header = {};
		if(path.indexOf(this.getBaseUrl()) == 0){
			header['Authorization'] = token;
			/*this.isStopRquest = token ? false : true;
			var pages = getCurrentPages();

			//console.log(pages[0].route+"------",token);
			if(!white && !token && pages && pages.length && pages[0].route != 'pages/login/login'){
				uni.redirectTo({
					url:"/pages/login/login"
				});
			}*/
		}
		return header;
	},
	__result(){
		return {
			success:null,
			fail:null,
			then(callback){
				this.success = callback;
				return this;
			},
			catch(callback){
				this.fail = callback;
				return this;
			}
		}
	},
	__rq(path,data,header,method,white){
		var token = uni.getStorageSync('token');
		path = this.getRequestUrl(path);
		var result = this.__result();
		var _this = this;
		header = this.getHeader(header,path,token,white);
		var time = (new Date()).getTime();
		
		//如果设置为停止请求，则忽略所有请求；请求超时允许为3秒；
		/*if((!token && !white) || (path == this.requestUrl && (time-this.lastRquestTime)<3000)){
			if(path == this.requestUrl){
				result = this.lastResult;
			}else{
				uni.hideLoading();
			}
			this.requestUrl = null;
			return result;
		}*/
		
		this.lastRquestTime = time;
		this.requestUrl = path;
		this.lastResult = result;
		
		// Add detailed logging for debugging
		console.log('=== API REQUEST START ===');
		console.log('URL:', path);
		console.log('Method:', method);
		console.log('Data:', data);
		console.log('Headers:', header);

		uni.request({
			url:path,
			data:data,
			method:method,
			header:header,
			success(res) {
				_this.requestUrl = null;
				uni.hideLoading();

				// Log the response
				console.log('=== API RESPONSE ===');
				console.log('Status Code:', res.statusCode);
				console.log('Response Data:', res.data);

				//如果是已停止请求，则忽略返回的数据
				if((_this.isStopRquest && !white)){
					console.log('Request stopped, ignoring response');
					return;
				}
				if(res.data.code == 500 && res.data.msg.indexOf("token")>0 && !white){
					if(!uni.getStorageSync("token")){
						return;
					}
					uni.getStorageSync("token",null);

					var pages = getCurrentPages();
					if(pages && pages.length && pages[0].route == '/pages/login/login'){
						return;
					}

					uni.navigateTo({
						url:"/pages/login/login"
					})
				}else if(res.data.code == 200 || res.data.code == 201){
					if(typeof result.success == 'function'){
						result.success(res.data);
					}
				}else{
					if(!white){
						uni.showToast({
							icon:"none",
							duration:2000,
							title:res.data.msg ? res.data.msg : '数据请求出错'
						});
					}

					if(typeof result.fail == 'function'){
						result.fail({message:res.data.msg ? res.data.msg : '数据请求出错',data:null,code:res.data.code});
					}
				}
			},
			fail(res) {
				_this.requestUrl = null;

				uni.hideLoading();

				// Log the error
				console.log('=== API REQUEST FAILED ===');
				console.log('Error:', res);

				//如果是已停止请求，则忽略返回的数据
				if(_this.isStopRquest && !white){
					return;
				}
				uni.showToast({
					icon:"none",
					duration:2000,
					title:"网络请求错误"
				});
				if(typeof result.fail == 'function'){
					result.fail({message:'网络请求错误',data:null,code:500});
				}
			}
		});
		
		return result;
	},
	
	__upload(path,data,header,white){
		var token = uni.getStorageSync('token');
		path = this.getRequestUrl(path);
		var result = this.__result();
		var _this = this;
		header = this.getHeader(header,path,token,white);
		var time = (new Date()).getTime();
		
		//如果设置为停止请求，则忽略所有请求；请求超时允许为3秒；
		if((!token && !white) || (path == this.requestUrl && (time-this.lastRquestTime)<3000)){
			if(path == this.requestUrl){
				result = this.lastResult;
			}else{
				uni.hideLoading();
			}
			this.requestUrl = null;
			return result;
		}
		
		this.lastRquestTime = time;
		this.requestUrl = path;
		this.lastResult = result;
		var params = {
			url:path,
			filePath:data.filePath ? data.filePath : null,
			name:data.name ? data.name : "file",
			formData:data.data ? data.data : null,
			header:header,
			success(res) {
				_this.requestUrl = null;
				uni.hideLoading();
				//如果是已停止请求，则忽略返回的数据
				if(_this.isStopRquest && !white){
					return;
				}
				if(res.data){
					try{
						res.data = JSON.parse(res.data);
					}catch(e){
						console.log("返回的不是JSON对象")
					}
				}
				if(res.data.code == 500 && res.data.msg.indexOf("token")>0 && !white){
					if(!uni.getStorageSync("token")){
						return;
					}
					uni.getStorageSync("token",null);
					
					var pages = getCurrentPages();
					if(pages && pages.length && pages[0].route == '/pages/login/login'){
						return;
					}
					
					uni.navigateTo({
						url:"/pages/login/login"
					})
				}else if(res.statusCode == 200 || res.statusCode == 201){
					if(typeof result.success == 'function'){
						result.success(res.data);
					}
				}else{
					uni.showToast({
						icon:"none",
						duration:2000,
						title:res.data.message ? res.data.message : '数据请求出错'
					});
					if(typeof result.fail == 'function'){
						result.fail({message:'数据请求出错',data:null,code:res.statusCode});
					}
				}
			},
			fail(res) {
				_this.requestUrl = null;
				
				uni.hideLoading();
				
				//如果是已停止请求，则忽略返回的数据
				if(_this.isStopRquest && !white){
					return;
				}
				uni.showToast({
					icon:"none",
					duration:2000,
					title:"网络请求错误"
				});
				if(typeof result.fail == 'function'){
					result.fail({message:'网络请求错误',data:null,code:500});
				}
			}
		}
		if(data.files){
			params.files = data.files;
		}
		
		uni.uploadFile(params);
		
		return result;
	},
	post(path,data,header,white){
		return this.__rq(path,data,header,'POST',white);
	},
	get(path,header,white){
		return this.__rq(path,null,header,'GET',white);
	},
	upload(path,data,header,white){
		return this.__upload(path,data,header,white);
	}
}

module.exports = request;
