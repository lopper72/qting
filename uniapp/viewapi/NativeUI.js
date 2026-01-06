const systemInfo = uni.getSystemInfoSync()
const pr = systemInfo.screenWidth/750

function getRealPx(val,nopx){
	if(val == 'wrap_content') return 'wrap_content'
	let _val = parseFloat(val)
	if((_val + "rpx") == val || (_val + "upx") == val){
		_val *= pr
		if(!nopx) _val += "px"
		return _val
	}
	return nopx ? _val : val
}

class Style{
	target = null
	__top = null
	__left = null
	__right = null
	__bottom = null
	__position = "absolute"
	__backgroundColor = null
	__backgroundImage = null
	__backgroundRepeat = "repeat"
	__backgroundPosition = "top left"
	__backgroundSize = "auto"
	__backgroundImageInfo = null
	__borderRadius = null
	__borderSize = null
	__borderColor = null
	__padding = null
	__margin = null
	__paddingLeft = null
	__paddingRight = null
	__paddingTop = null
	__paddingBottom = null
	__marginTop = null
	__marginLeft = null
	__marginRight = null
	__marginBottom = null
	__width = null
	__height = null
	__opacity = 1
	__color = null
	__textAlign = "left"
	__textVerticalAlign = "top"
	__whiteSpace = "normal"
	__textOverflow = "clip"
	__fontSize = "12px"
	__fontFamily = null
	__fontSrc = null
	__lineSpacing = null
	__fontStyle = "normal"
	__fontWeight = "normal"
	__fontDecoration = "none"
	__changeEvent = null
	
	constructor(target){
		this.target = target
	}

	
	set top(val){
		this.__top = getRealPx(val)
		this.fireChange("style")
	}
	get top(){
		return this.__top
	}
	
	set left(val){
		this.__left = getRealPx(val)
		this.fireChange("style")
	}
	get left(){
		return this.__left
	}
	
	set marginLeft(val){
		this.__marginLeft = getRealPx(val)
		this.fireChange("style")
	}
	get marginLeft(){
		return this.__marginLeft
	}
	
	set borderRadius(val){
		this.__borderRadius = getRealPx(val)
		this.fireChange("style")
	}
	get borderRadius(){
		return this.__borderRadius
	}
	
	set borderSize(val){
		this.__borderSize = getRealPx(val)
		this.fireChange("style")
	}
	get borderSize(){
		return this.__borderSize
	}
	
	set borderColor(val){
		this.__borderSize = val
		this.fireChange("style")
	}
	get borderColor(){
		return this.__borderColor
	}
	set margin(val){
		this.__margin = getRealPx(val)
		this.fireChange("style")
	}
	get margin(){
		return this.__margin
	}
	
	set marginTop(val){
		this.__marginTop = getRealPx(val)
		this.fireChange("style")
	}
	get marginTop(){
		return this.__marginTop
	}
	
	set marginBottom(val){
		this.__marginBottom = getRealPx(val)
		this.fireChange("style")
	}
	get marginBottom(){
		return this.__marginBottom
	}
	
	set marginRight(val){
		this.__marginRight = getRealPx(val)
		this.fireChange("style")
	}
	get marginRight(){
		return this.__marginRight
	}
	
	set padding(val){
		this.__padding = getRealPx(val)
		this.fireChange("style")
	}
	get padding(){
		return this.__padding
	}
	
	set paddingTop(val){
		this.__paddingTop = getRealPx(val)
		this.fireChange("style")
	}
	get paddingTop(){
		return this.__paddingTop
	}
	
	set paddingLeft(val){
		this.__paddingLeft = getRealPx(val)
		this.fireChange("style")
	}
	get paddingLeft(){
		return this.__paddingLeft
	}
	
	set paddingRight(val){
		this.__paddingRight = getRealPx(val)
		this.fireChange("style")
	}
	get paddingRight(){
		return this.__paddingRight
	}
	
	set paddingBottom(val){
		this.__paddingBottom = getRealPx(val)
		this.fireChange("style")
	}
	get paddingBottom(){
		return this.__paddingBottom
	}
	
	set right(val){
		this.__right = getRealPx(val)
		this.fireChange("style")
	}
	get right(){
		return this.__right
	}
	
	set bottom(val){
		this.__bottom = getRealPx(val)
		this.fireChange("style")
	}
	get bottom(){
		return this.__bottom
	}
	
	set backgroundColor(val){
		this.__backgroundColor = val
		this.fireChange("bg")
	}
	get backgroundColor(){
		return this.__backgroundColor
	}
	
	set backgroundImage(val){
		this.__backgroundImage = val
		this.__backgroundImageInfo = null
		this.fireChange("bg")
	}
	get backgroundImage(){
		return this.__backgroundImage
	}
	
	set backgroundRepeat(val){
		val = val.toLocaleLowerCase()
		let types = ["repeat","repeat-x","repeat-y","no-repeat","inherit"]
		if(types.indexOf(val) == -1) return
		this.__backgroundRepeat = val
		this.fireChange("bg")
	}
	get backgroundRepeat(){
		return this.__backgroundRepeat == "inherit" && (!this.target || !this.target.parent) ?  "repeat" : this.__backgroundRepeat
	}
	
	set backgroundPosition(val){
		this.__backgroundPosition = val.toLocaleLowerCase()
		this.fireChange("bg")
	}
	get backgroundPosition(){
		return this.__backgroundPosition
	}
	
	set backgroundSize(val){
		val = val.toLocaleLowerCase()
		let types = ["atuo","cover","contain"]
		if(types.indexOf(val) == -1) return
		this.__backgroundSize = val
		this.fireChange("bg")
	}
	get backgroundSize(){
		return this.__backgroundSize
	}
	
	set width(val){
		this.__width = getRealPx(val)
		this.fireChange("style")
	}
	get width(){
		return this.__width
	}
	
	set height(val){
		val = val.toLocaleLowerCase()
		this.__height = getRealPx(val)
		this.fireChange("style")
	}
	get height(){
		return this.__height
	}
	
	set position(val){
		let posType = ["static","absolute","dock"]
		val = val.toLowerCase();
		if(posType.indexOf(val) == -1) return;
		this.__position = val
		this.fireChange("style")
	}
	get position(){
		return this.__position
	}
	
	
	set opacity(val){
		this.__opacity = val
		this.fireChange("style")
	}
	get opacity(){
		return this.__opacity
	}
	
	set color(val){
		this.__color = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get color(){
		return this.__color
	}
	
	set textAlign(val){
		let type = ["left","right","center"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__textAlign = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get textAlign(){
		return this.__textAlign
	}
	
	set textValign(val){
		let type = ["top","middle","bottom"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__textVerticalAlign = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get textValign(){
		return this.__textVerticalAlign
	}
	
	set whiteSpace(val){
		let type = ["normal","nowrap"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__whiteSpace = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get whiteSpace(){
		return this.__whiteSpace
	}
	
	set textOverflow(val){
		let type = ["clip","ellipsis"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__textOverflow = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get textOverflow(){
		return this.__textOverflow
	}
	
	set fontStyle(val){
		let type = ["normal","italic"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__fontStyle = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get fontStyle(){
		return this.__fontStyle
	}
	
	set fontWeight(val){
		let type = ["normal","bold"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__fontWeight = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get fontWeight(){
		return this.__fontWeight
	}
	
	set fontDecoration(val){
		let type = ["none","underline","line-through"]
		val = val.toLowerCase();
		if(type.indexOf(val) == -1) return;
		this.__fontDecoration = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	
	get fontDecoration(){
		return this.__fontDecoration
	}
	
	set fontSize(val){
		this.__fontSize = getRealPx(val)
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get fontSize(){
		return this.__fontSize
	}
	
	set fontFamily(val){
		this.__fontFamily = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get fontFamily(){
		return this.__fontFamily
	}
	
	set fontSrc(val){
		this.__fontSrc = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get fontSrc(){
		return this.__fontSrc
	}
	
	set lineSpacing(val){
		this.__lineSpacing = val
		if(this.target && this.target.innerText) this.fireChange("text")
	}
	get lineSpacing(){
		return this.__lineSpacing
	}
	
	onChange(callback){
		this.__changeEvent = callback
	}
	fireChange(type){
		if(typeof this.__changeEvent == 'function') this.__changeEvent(type)
	}
}
class UI{
	__parent = null
	__view = null
	__children = []
	__id = null
	__events = new Map()
	__show = false
	__startTop = 0
	__startLeft = 0
	tagName = "view"
	style = new Style(this)
	__animationTimer = null
	
	constructor(id) {
		if(id){
			this.id = id
		}else{
			this.id = this.getRandStr(10)
		}
		this.__view = new plus.nativeObj.View()
		this.style.onChange((type)=>{
			switch(type){
				case "style":
					this.__drawStyle()
					return
				case "bg":
					//this.__drawBg()
					//return
				case "text":
					this.__drawText()
					return
			}
			this.update()
		})
	}
	
	set id(val){
		this.__id = val
	}
	get id(){
		return this.__id
	}
	
	getRandStr(length){
		let str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
		let rand = ""
		for(var i = 0;i < length;i++){
			rand += str.charAt(Math.floor(Math.random() * length))
		}
		return rand
	}
	
	set parent(val){
		this.__parent = val
		val.appendChild(this)
		this.update()
	}
	get parent(){
		return this.__parent
	}
	
	__getRealSize(val,max){
		if(val == 'wrap_content') return 'wrap_content'
		if(val === null) return null
		let _val = parseFloat(val)
		if((_val + "px") == val) return _val
		if((_val + "%") == val){
			return _val/100 * max
		}
		return null
	}
	
	/**
	 * 获取组件显示宽度
	 */
	getWidth(){
		let maxWidth = this.parent ? this.parent.getWidth() : systemInfo.screenWidth
		let left = this.__getRealSize(this.style.left,maxWidth)
		let right = this.__getRealSize(this.style.right,maxWidth)
		let width = this.__getRealSize(this.style.width,maxWidth)
		let marginLeft = this.style.marginLeft ? this.__getRealSize(this.style.marginLeft,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
		let marginRight = this.style.marginRight ? this.__getRealSize(this.style.marginRight,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
		let margin = marginLeft && marginRight ? marginLeft + marginRight : 0
		
		if(width !== null){
			return width - margin
		}
		if(left === null) left = 0
		if(right === null) right = 0
		right = maxWidth - right
		width = right - left - margin
		
		return width>0 ? width : 0
	}
	
	
	/**
	 * 获取组件显示高度
	 */
	getHeight(){
		if(this.style.height == 'wrap_content') return 'wrap_content'
		let maxHeight = this.parent ? this.parent.getHeight() : systemInfo.screenHeight
		let top = this.__getRealSize(this.style.top,maxHeight)
		let bottom = this.__getRealSize(this.style.bottom,maxHeight)
		let height = this.__getRealSize(this.style.height,maxHeight)
		let marginTop = this.style.marginTop ? this.__getRealSize(this.style.marginTop,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		let marginBottom = this.style.marginBottom ? this.__getRealSize(this.style.marginBottom,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		let margin = marginTop && marginBottom ? marginTop + marginBottom : 0
		if(height !== null) return height - margin
		if(top === null) top = 0
		if(bottom === null) bottom = 0
		bottom = maxHeight - bottom
		height = bottom - top - margin
		return height>0 ? height : 0
	}
	
	/**
	 * 获取组件top值
	 */
	getTop(){
		let maxHeight = this.parent ? this.parent.getHeight() : this.getHeight()
		let top = this.__getRealSize(this.style.top,maxHeight)
		
		let marginTop = this.style.marginTop ? this.__getRealSize(this.style.marginTop,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		let marginBottom = this.style.marginBottom ? this.__getRealSize(this.style.marginBottom,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		if(top === null) top = 0
		
		if(marginTop) top += marginTop
		if(this.parent){
			let parentTop = parseFloat(this.parent.getTop())
			let parentHeight = parseFloat(this.parent.getHeight())
			let height = parseFloat(this.getHeight())
			
			if(this.style.bottom){
				let bottom = this.__getRealSize(this.style.bottom,maxHeight)
				return (this.__startTop + parentTop + top + (parentHeight - height) - bottom) + "px"
			}else{
				return (this.__startTop + parentTop + top) + "px"
			}
			
		}else{
			top += this.__startTop
			if(marginBottom<0) top -= marginBottom
			return top + "px"
		}
		
	}
	
	/**
	 * 获取组件bottom值
	 */
	getBottom(){
		let maxHeight = this.parent ? this.parent.getHeight() : systemInfo.screenHeight
		let bottom = this.__getRealSize(this.style.bottom,maxHeight)
		let marginTop = this.style.marginTop ? this.__getRealSize(this.style.marginTop,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		let marginBottom = this.style.marginBottom ? this.__getRealSize(this.style.marginBottom,maxHeight) : this.__getRealSize(this.style.margin,maxHeight)
		if(bottom === null) bottom = 0
		if(marginBottom) bottom += marginBottom
		if(this.parent){
			let parentBottom = parseFloat(this.parent.getBottom())
			return (bottom + parentBottom) + "px"
		}else{
			if(marginTop<0) bottom -= marginTop
			return bottom + "px"
		}
	}
	
	/**
	 * 获取组件left
	 */
	getLeft(){
		let maxWidth = this.parent ? this.parent.getWidth() : systemInfo.screenWidth
		let left = this.__getRealSize(this.style.left,maxWidth)
		
		let marginLeft = this.style.marginLeft ? this.__getRealSize(this.style.marginLeft,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
		let marginRight = this.style.marginRight ? this.__getRealSize(this.style.marginRight,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
		if(left === null) left = 0
		if(marginLeft) left += marginLeft
		if(this.parent){
			let parentLeft = parseFloat(this.parent.getLeft())
			let parentWidth = parseFloat(this.parent.getWidth())
			let width = parseFloat(this.getWidth())
			if(this.style.right){
				let right = this.__getRealSize(this.style.right,maxWidth)
				
				return (parentLeft + left + (parentWidth - width) - right) + "px"
			}else{
				return (parentLeft + left) + "px"
			}
		}else{
			if(marginRight<0) left -= marginRight
			return left + "px"
		}
	}
	
	/**
	 * 获取组件right
	 */
	getRight(){
		let maxWidth = this.parent ? this.parent.getWidth() : systemInfo.screenWidth
		let right = this.__getRealSize(this.style.right,maxWidth)
		let marginLeft = this.style.marginLeft ? this.__getRealSize(this.style.marginLeft,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
		let marginRight = this.style.marginRight ? this.__getRealSize(this.style.marginRight,maxWidth) : this.__getRealSize(this.style.margin,maxWidth)
	
		if(right === null) right = 0
		if(marginRight) right += marginRight
		if(this.parent){
			let parentRight = parseFloat(this.parent.getRight())
			
			return (right + parentRight) + "px"
		}else{
			if(marginLeft<0) right -= marginLeft
			return right + "px"
		}
	}
	
	
	/**
	 * 添加子组件
	 * 
	 * @param {Object} view
	 */
	appendChild(view){
		this.__children.push(view)
		view.__parent = this
		view.update()
	}
	
	/**
	 * 通过ID获取组件
	 * 
	 * @param {Object} id
	 */
	getElementById(id){
		for(var i = 0;i < this.__children.length;i++){
			var view = this.__children[i]
			if(view.id == id) return view
		}
		return null;
	}
	
	
	/**
	 * 添加事件监听
	 * 
	 * @param {Object} event
	 * @param {Object} listener
	 * @param {Object} capture
	 */
	addEventListener(event,listener,capture){
		this.__view.addEventListener(event,(e)=>{
			e.target = this
			if(typeof listener == 'function') listener(e) 
		},capture)
	}
	
	/**
	 * 更新组件的显示
	 */
	update(){
		if(!this.__show) return
		
		this.clear()
		
		this.__drawStyle()
		
		if(this.style.__backgroundImageInfo){
			this.__drawBg(this.style.__backgroundImageInfo)
			this.__drawText()
		}else if(this.style.backgroundImage){
			uni.getImageInfo({
				src: this.style.backgroundImage,
				success:(image)=>{
					if(!image.width || !image.height) return
					this.style.__backgroundImageInfo = image
					this.__drawBg(image)
					this.__drawText()
				}
			});
		}else{
			this.__drawText()
		}
		
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.update()
		}
	}
	
	/**
	 * 设置显示样式
	 */
	__drawStyle(){
		let maxWidth = this.parent ? this.parent.getWidth() : systemInfo.screenWidth
		let maxHeight = this.parent ? this.parent.getHeight() : systemInfo.screenHeight
		let top = this.getTop()
		let left = this.getLeft()
		let right = this.getRight()
		let bottom = this.getBottom()
		let width = this.getWidth()
		let height = this.getHeight()
		
		if(width !== null) width += "px"
		if(height !== null && height != 'wrap_content') height = (parseInt(height * 100) /100) + "px"
		var options = {
			backgroundColor:this.style.borderRadius ? 'rgba(255,255,255,0)' : this.style.backgroundColor,
			width:width,
			height:height,
			top:top,
			left:left,
			right:right,
			opacity:this.style.opacity !== null ? this.style.opacity : 1,
			position:this.style.position
		}
		if(!height || height == 'wrap_content'){
			options['bottom'] = bottom
		}
		this.__view.setStyle(options)
		if(this.style.borderRadius){
			this.clear()
			this.__view.drawRect({
				color:this.style.backgroundColor,
				radius:this.style.borderRadius,
				borderColor:this.style.borderColor,
				borderWidth:this.style.borderSize
			})
			if(this.style.__backgroundImageInfo){
				this.__drawBg(this.style.__backgroundImageInfo)
				this.__drawText()
			}else if(this.style.backgroundImage){
				uni.getImageInfo({
					src: this.style.backgroundImage,
					success:(image)=>{
						if(!image.width || !image.height) return
						this.style.__backgroundImageInfo = image
						this.__drawBg(image)
						this.__drawText()
					}
				});
			}else{
				this.__drawText()
			}
		}
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.__drawStyle()
		}
	}
	
	/**
	 * 绘制背景图片
	 */
	__drawBg(image){
		let width = this.getWidth()
		let height = this.getHeight()
		let size = this.style.backgroundSize
		if(size == "contain"){
			this.__view.drawBitmap(image.path)
			return
		}else if(size == "cover"){
			let left = 0
			let top = 0
			let _width = image.width
			let _height = image.height
			if(width / _width * _height >= height){
				_height = width / _width * _height
				_width = width
			}else{
				_width = height/_height * _width
				_height = height
			}
			left = (width - _width) / 2
			top = (height - _height) / 2
			
			this.__view.drawBitmap(image.path,{width:_width + "px",height:_height + "px"},{top:top + "px",left:left + 'px',width:_width + "px",height:_height + "px"})
			return
		}
		
		let repeat = this.style.backgroundRepeat
		if(repeat == "inherit" && this.parent) repeat = this.parent.style.backgroundRepeat
		if(repeat == "no-repeat"){
			let position = this.style.backgroundPosition
			let [_top,_left] = position.split(" ")
			let top = 0
			let left = 0
			
			if(((_top == "top" || _top == "center" || _top == "bottom") && typeof _left == 'undefined') || _left == "center"){
				left = (width - image.width) / 2
			}
			if(_top == "center" || ((_top == "right" || _top == "left") && typeof _left == 'undefined')){
				if(_top == "right") left = width - image.width
				top = (height - image.height) / 2
			}
			if(_top == "top" || _top == "" || _left == "top") top = 0
			if(_left == "left" || _left == "") left = 0
			if(_top == "bottom" || _left == "bottom") top = height - image.height
			if(_top == "right" || _left == "right") left = width - image.width
			if(_top && _top.indexOf("px") != -1){
				top = getRealPx(_top,true)
			}
			if(_left && _left.indexOf("px") != -1){
				left = getRealPx(_left,true)
			}
			if(_top && _top.indexOf("%") != -1){
				top = parseFloat(_top)/100 * height
			}
			if(_left && _left.indexOf("%") != -1){
				left = parseFloat(_left)/100 * width
			}
			
			this.__view.drawBitmap(image.path,{width:image.width+"px",height:image.height+"px"},{top:top + "px",left:left + 'px',width:image.width+"px",height:image.height+"px"})
		}else if(repeat == "repeat-x"){
			let wcount = Math.ceil(width/image.width)
			for(let i = 0;i < wcount;i++){
				this.__view.drawBitmap(image.path,{width:image.width+"px",height:image.height+"px"},{left:(image.width * i) + 'px',width:image.width+"px",height:image.height+"px"})
			}
		}else if(repeat == "repeat-y"){
			let hcount = Math.ceil(height/image.height)
			for(let i = 0;i < hcount;i++){
				this.__view.drawBitmap(image.path,{width:image.width+"px",height:image.height+"px"},{top:(image.height * i) + 'px',width:image.width+"px",height:image.height+"px"})
			}
		}else if(repeat == "repeat"){
			let hcount = Math.ceil(height/image.height)
			for(let h = 0;h < hcount;h++){
				let wcount = Math.ceil(width/image.width)
				for(let i = 0;i < wcount;i++){
					this.__view.drawBitmap(image.path,{width:image.width+"px",height:image.height+"px"},{top:(image.height * h) + 'px',left:(image.width * i) + 'px',width:image.width+"px",height:image.height+"px"})
				}
			}
		}
		
	}
	
	__drawText(){
		let maxWidth = this.getWidth()
		let maxHeight = this.getHeight()
		let paddingLeft = this.style.paddingLeft ? this.__getRealSize(this.style.paddingLeft,maxWidth) : this.__getRealSize(this.style.padding,maxWidth)
		let paddingTop = this.style.paddingTop ?  this.__getRealSize(this.style.paddingTop,maxHeight) : this.__getRealSize(this.style.padding,maxHeight)
		let paddingRight = this.style.paddingRight ? this.__getRealSize(this.style.paddingRight,maxWidth) : this.__getRealSize(this.style.padding,maxWidth)
		let paddingBottom = this.style.paddingBottom ? this.__getRealSize(this.style.paddingBottom,maxHeight) : this.__getRealSize(this.style.padding,maxHeight)
		if(this.innerText){
			this.__view.drawText(this.innerText,{
				top:paddingTop + "px",
				left:paddingLeft + "px",
				width:(maxWidth - paddingLeft - paddingRight) + "px",
				height:maxHeight != 'wrap_content' ? (maxHeight - paddingTop -paddingBottom) + "px" : 'wrap_content'},{
					color:this.style.color,
					align:this.style.textAlign,
					verticalAlign:this.style.textValign,
					whiteSpace:this.style.whiteSpace,
					overflow:this.style.textOverflow,
					size:this.style.fontSize,
					family:this.style.fontFamily,
					fontSrc:this.style.fontSrc,
					lineSpacing:this.style.lineSpacing,
					style:this.style.fontStyle,
					weight:this.style.fontWeight,
					decoration:this.style.fontDecoration
				})
		}else if(this.innerHTML){
			let _this = this
			this.__view.drawRichText(this.innerHTML,{
				top:paddingTop + "px",
				left:paddingLeft + "px",
				width:(maxWidth - paddingLeft - paddingRight) + "px",
				height:maxHeight != 'wrap_content' ? (maxHeight - paddingTop -paddingBottom) + "px" : 'wrap_content'},{
				onClick(e) {
					if(e.tagName == 'a' && e.href){
						//console.log(e)
						let href = e.href.toLocaleLowerCase()
						if(href.indexOf("http://") == 0 || href.indexOf("https://") == 0){
							_this.close()
							plus.runtime.openURL(e.href)
						}else if(href.indexOf("/pages/") == 0){
							_this.close()
							uni.navigateTo({
								url:href,
								fail(f) {
									console.log(f)
								}
							})
						}
						
					}
				}
			})
		}
	}
	
	/**
	 * 显示组件
	 */
	show(){
		this.__show = true
		this.__view.show()
		this.update()
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.show()
		}
	}
	
	/**
	 * 隐藏组件
	 */
	hide(){
		this.__show = false
		this.__view.hide()
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.hide()
		}
	}
	
	close(){
		this.__show = false
		this.__view.close()
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.close()
		}
	}
	
	/**
	 * 清除显示
	 */
	clear(){
		this.__view.reset()
		//this.__view.clearRect()
	}
	
	/**
	 * 销毁组件
	 */
	destory(){
		this.__view.close()
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.destory()
		}
		this.__children = []
	}
	
	/**
	 * 开始动画
	 * 
	 * @param {Object} options
	 * @param {Object} callback
	 * @param {Object} otherview
	 */
	animation(options,callback,otherview){
		if(otherview){
			plus.nativeObj.View.startAnimation(options, otherview,this.__view, callback)
		}else{
			plus.nativeObj.View.startAnimation(options, this.__view, null, callback)
		}
	}
	
	/**
	 * 关闭动画
	 * 
	 * @param {Object} type
	 */
	clearAnimation(type){
		plus.nativeObj.View.clearAnimation(type)
	}
	
	/**
	 * 淡入
	 * 
	 * @param {Object} speed
	 * @param {Object} callback
	 */
	fadeIn(speed,callback){
		this.show()
		clearInterval(this.__animationTimer)
		
		let time = typeof speed == "number" ? speed : 1000
		if(speed == "slow") time = 2000
		if(speed == "normal") time = 1000
		if(speed == "fast") time = 500
		
		let opacity = this.style.opacity === null ? 1 : this.style.opacity
		
		if(time>0 && opacity>0){
			this.style.opacity = 0
			let _time = 10
			let _times = time/_time
			let g = opacity/(_times * _times) * 2
			let t = 0
			
			this.__animationTimer = setInterval(()=>{
				t++
				this.style.opacity += t * g
				if(this.style.opacity>=opacity){
					clearInterval(this.__animationTimer)
					if(typeof callback == 'function') callback()
				}
			},_time)
		}
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.fadeIn(speed)
		}
	}
	
	/**
	 * 淡出
	 * 
	 * @param {Object} speed
	 * @param {Object} callback
	 */
	fadeOut(speed,callback){
		if(!this.__show) return
		let time = typeof speed == "number" ? speed : 1000
		if(speed == "slow") time = 1000
		if(speed == "normal") time = 500
		if(speed == "fast") time = 200
		
		let opacity = this.style.opacity === null ? 1 : this.style.opacity
		
		if(time>0 && opacity>0){
			let _time = 10
			let _times = time/_time
			let g = opacity/(_times * _times) * 2
			let t = _times
			
			this.__animationTimer = setInterval(()=>{
				t--
				this.style.opacity -= g * t
				if(t <= 0){
					clearInterval(this.__animationTimer)
					this.close()
					this.style.opacity = opacity
					if(typeof callback == 'function') callback()
				}
			},_time)
		}
		for(var i=0;i<this.__children.length;i++){
			let view = this.__children[i]
			view.fadeOut(speed)
		}
	}
	
	/**
	 * 上滑动画
	 * 
	 * @param {type} height 
	 * @param {Object} speed
	 * @param {Object} callback
	 */
	slideUp(height,speed,callback){
		if(!this.__show) return
		let time = typeof speed == "number" ? speed : 1000
		if(speed == "slow") time = 1000
		if(speed == "normal") time = 500
		if(speed == "fast") time = 200
		
		height = typeof height == 'number' ? height : systemInfo.screenHeight
		
		if(time>0 && height>0){
			let _time = 10
			let _times = time/_time
			let g = height/(_times * _times) * 2
			let t = 0
			this.__animationTimer = setInterval(()=>{
				t++
				let startTop = this.__startTop
				this.__startTop = startTop - g * t
				this.__drawStyle()
				if(t>=_times){
					clearInterval(this.__animationTimer)
					if(typeof callback == 'function') callback()
					return
				}
			},_time)
		}
	}
	
	/**
	 * 下滑动画
	 * 
	 * @param {type} height 
	 * @param {Object} speed
	 * @param {Object} callback
	 */
	slideDown(height,speed,callback){
		if(!this.__show) return
		let time = typeof speed == "number" ? speed : 1000
		if(speed == "slow") time = 1000
		if(speed == "normal") time = 500
		if(speed == "fast") time = 200
		
		height = typeof height == 'number' ? height : systemInfo.screenHeight
		if(time>0 && height>0){
			let _time = 10
			let _times = time/_time
			let g = height/(_times * _times) * 2
			let t = 0
			
			this.__animationTimer = setInterval(()=>{
				t++
				let startTop = this.__startTop
				this.__startTop = startTop + g * t
				this.__drawStyle()
				if(t>=_times){
					clearInterval(this.__animationTimer)
					if(typeof callback == 'function') callback()
					return
				}
			},_time)
		}
	}
}
let View = class View extends UI{
	__text = null
	__html = null
	
	set innerText(val){
		this.__text = val
		this.__html = null
		this.update()
	}
	get innerText(){
		return this.__text
	}
	
	set innerHTML(val){
		this.__text = null
		this.__html = val
		this.update()
	}
	get innerHTML(){
		return this.__html
	}
}

let ImageSlier = class ImageSlier extends UI{
	__view = null
	
	constructor(id,styles) {
	    super(id)
		
	    this.__view = new plus.nativeObj.ImageSlider(null,styles);
	    this.style.onChange((type)=>{
	    	switch(type){
	    		case "style":
	    			this.__drawStyle()
	    			return
	    		case "bg":
	    			//this.__drawBg()
	    			//return
	    		case "text":
	    			this.__drawText()
	    			return
	    	}
	    	this.update()
	    })
	}
	
	addImages(images){
		this.__view.addImages(images)
	}
	
	setImages(images){
		this.__view.setImages(images)
	}
	
	currentImageIndex(){
		return this.__view.currentImageIndex()
	}
}
let Swiper = class Swiper extends UI{
	__images = []
	__index = 0
	__touched = null
	__leftStart = 0
	__moveStart = 0
	__events = {}
	__playTimer = null
	__options = {
		current:0,
		repeat:true,
		bounce:true,
		showTitle:true,
		titleHeight:'20px',
		titleColor:"#FFFFFF"
	}
	
	constructor(id,options){
		super(id)
		
		if(typeof options == 'object'){
			for(let key in options){
				if(typeof this.__options[key] != 'undefined'){
					this.__options[key] = options[key]
				}
			}
		}
		
		this.style.backgroundColor = "#FFFFFF"
		this.__view.addEventListener("touchstart",(e)=>{
			this.__couched = e
			this.__moveStart = e.screenX
			if(typeof this.__events["touchstart"].listener == 'function') this.__events["touchstart"].listener(e) 
		});
		
		this.__view.addEventListener("touchend",(e)=>{
			this.__couched = null
			let moveWidth = this.__moveStart - e.screenX
			let width = this.getWidth()
			if(moveWidth>0){
				if((!this.__options.repeat && this.index >= (this.__images.length -1)) || Math.abs(moveWidth) * 4 < width){
					this.__moveTo(this.index * width * -1)
				}else{
					this.__moveTo((this.index+1) * width * -1)
				}
				
			}else{
				if((!this.__options.repeat && this.index == 0) || Math.abs(moveWidth) * 4 < width){
					this.__moveTo(this.index * width * -1)
				}else{
					this.__moveTo((this.index - 1) * width * -1)
				}
			}
			if(typeof this.__events["touchend"].listener == 'function') this.__events["touchend"].listener(e) 
		});
		
		this.__view.addEventListener("touchmove",(e)=>{
			let touched = this.__couched
			let moveWidth = e.screenX - touched.screenX
			this.__leftStart += moveWidth
			if(!this.__options.repeat && !this.__options.bounce){
				let width = this.getWidth()
				let length = this.__images.length
				if(this.index == 0 && this.__leftStart>0){
					this.__leftStart = 0
				}
				if((this.index+1) >= length && (this.__leftStart * -1) > this.index * width){
					this.__leftStart = this.index * width * -1
				}
			}
			this.__couched = e
			this.__drawImages()
			if(typeof this.__events["touchmove"].listener == 'function') this.__events["touchmove"].listener(e) 
		});
	}
	
	/**
	 * 添加一张图片
	 * 
	 * image = {
	 *	 src:"http://www.***.png"
	 * 	 title:"张三"
	 * }
	 * 
	 * @param {Object} image
	 */
	addImage(image){
		this.__images.push(image)
		//默认显示位置
		if(this.__options.current>0 && this.__options.current<this.__images.length){
			this.__leftStart = this.__options.current * this.getWidth() * -1
			this.index = this.__options.current
		} 
		this.update()
	}
	
	/**
	 * 添加多张图片
	 * 
	 * @param {Object} images
	 */
	addImages(images){
		this.__images = this.__images.concat(images)
		//默认显示位置
		if(this.__options.current>0 && this.__options.current<this.__images.length){
			this.__leftStart = this.__options.current * this.getWidth() * -1
			this.index = this.__options.current
		} 
		this.update()
	}
	
	/**
	 * 获取当前图片索引
	 */
	getCurrentIndex(){
		return this.index
	}
	
	/**
	 * 监听变化事件
	 * 
	 * @param {Object} callback
	 */
	onChange(callback){
		this.__events['change'] = callback
	}
	
	get index(){
		return this.__index
	}
	
	set index(val){
		this.__index = val
	}
	
	/**
	 * 重写添加事件监听
	 * 
	 * @param {Object} event
	 * @param {Object} listener
	 * @param {Object} capture
	 */
	addEventListener(event,listener,capture){
		this.__events[event] = {
			listener,
			capture
		}
		var current = this.__images[this.index]
		this.__view.addEventListener(event,(e)=>{
			e.target = this
			e['current'] = current
			if(typeof this.__events[event].listener == 'function') this.__events[event].listener(e) 
		},capture)
	}
	
	update(){
		super.update()
		this.__drawImages()
	}
	
	__drawImages(){
		//把第一张图片在其左右再绘制一张，以实现无缝切换
		if(this.__options.repeat && this.__images.length){
			let image = this.__images[this.__images.length - 1]
			
			this.__view.drawBitmap(image.src,{
				width:"100%",
				height:"100%"
			},{
				left:(this.__leftStart + -1 * this.getWidth()) + "px",
				width:this.getWidth() + "px",
				height:this.getHeight() + "px"
			},this.id + "_start")
		}
		for(var i = 0;i < this.__images.length;i++){
			let image = this.__images[i]
			
			this.__view.drawBitmap(image.src,{
				width:"100%",
				height:"100%"
			},{
				left:(this.__leftStart + i * this.getWidth()) + "px",
				width:this.getWidth() + "px",
				height:this.getHeight() + "px"
			},this.id + "_" + i)
		}
		//把最后一张图片在其左右再绘制一张，以实现无缝切换
		if(this.__options.repeat && this.__images.length){
			let image = this.__images[0]
			
			this.__view.drawBitmap(image.src,{
				width:"100%",
				height:"100%"
			},{
				left:(this.__leftStart + this.__images.length * this.getWidth()) + "px",
				width:this.getWidth() + "px",
				height:this.getHeight() + "px"
			},this.id + "_end")
		}
	}
	
	moveToIndex(index){
		let toWidth = (this.index - index) * width
		this.__moveTo(toWidth)
	}
	__moveTo(toWidth){
		clearInterval(this.__playTimer)
		let width = this.getWidth()
		let changeSize = width/30
		if(changeSize<=0 || width == 0 || this.__images.length == 0) return
		if(toWidth<this.__leftStart) changeSize *= -1
		
		this.__playTimer = setInterval(()=>{
			this.__leftStart += changeSize
			if(changeSize<0){
				if(this.__leftStart <= toWidth){
					this.__leftStart = toWidth
					this.index =Math.ceil(this.__leftStart/width) * -1
					
					if(this.index>=this.__images.length){
						
						this.index = 0;
						this.__leftStart = 0
					}
					clearInterval(this.__playTimer)
					if(typeof this.__events['change'] == 'function') this.__events['change'](this.index)
				}
			}else{
				if(this.__leftStart >= toWidth){
					this.__leftStart = toWidth
					this.index =Math.ceil(this.__leftStart/width) * -1
					if(this.index<0){
						this.index = this.__images.length - 1
						//console.log(this.index)
						this.__leftStart = this.index * width * -1
					}
					
					clearInterval(this.__playTimer)
				}
			}
			
			this.__drawImages()
		},10)
	}
}

export default {View,ImageSlier,Swiper,version:"1.0"}