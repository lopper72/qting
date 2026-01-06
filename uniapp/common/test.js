import { config } from '@/js_sdk/index.js'
import MD5 from 'crypto-js/md5'

let n = ''
let app_key = ''
let customer_code = ''
let isOpen = '0'

config({ key: 'safe' }).then(({ code, data }) => {
	if (code === 200) {
		const { cy_n, cy_app_key, cy_customer_code, cy_status } = data
		n = cy_n
		app_key = cy_app_key
		customer_code = cy_customer_code
		isOpen = cy_status
	}
})

const Test = (type, content) => {
	const content_id = new Date().getTime()
	const s = parseInt(content_id / 1000)
	const t = MD5(`${s}${app_key}`).toString()
	
	return new Promise((resolve, reject) => {
		if (isOpen === '0') {
			resolve({ machine_result: 'Normal' })
		} else {
			uni.request({
			    url: `https://kmscli.qixincha.com/client/purify/${type}/?n=${n}&t=${t}&s=${s}`,
				method: 'POST',
			    data: {
					customer_code,
					content_id,
					content
				},
			    header: {
					'content-type': 'application/x-www-form-urlencoded'
				},
			    success: ({ data }) => {
					resolve(data)
			    },
				fail: (err) => {
					reject(err)
				},
				complete: () => {
					console.log('complete')
				}
			})
		}
	})
}

export default Test