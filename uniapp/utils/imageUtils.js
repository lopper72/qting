import config from '@/common/config.js'

/**
 * Transforms image URLs to use the appropriate domain based on the environment
 * @param {string} url - The original image URL
 * @returns {string} - The transformed image URL
 */
export function transformImageUrl(url) {
	if (!url) return url

	// Replace production domain with the configured image base URL
	return url.replace('http://qingtingcun.youyacao.com/', config.image_base)
}

/**
 * Transforms an object containing image URLs (simple version without deep cloning)
 * @param {Object} obj - The object containing image URLs
 * @param {string[]} imageFields - Array of field names that contain image URLs
 * @returns {Object} - The object with transformed image URLs
 */
export function transformImageUrlsInObject(obj, imageFields = ['thumb', 'avatar', 'images']) {
	if (!obj) return obj

	imageFields.forEach(field => {
		if (obj[field]) {
			if (Array.isArray(obj[field])) {
				// Handle array of images
				for (let i = 0; i < obj[field].length; i++) {
					obj[field][i] = transformImageUrl(obj[field][i])
				}
			} else {
				// Handle single image URL
				obj[field] = transformImageUrl(obj[field])
			}
		}
	})

	return obj
}

/**
 * Transforms image URLs in an array of objects (simple version without deep cloning)
 * @param {Array} array - The array of objects containing image URLs
 * @param {string[]} imageFields - Array of field names that contain image URLs
 * @returns {Array} - The array with transformed image URLs
 */
export function transformImageUrlsInArray(array, imageFields = ['thumb', 'avatar', 'images']) {
	if (!array || !Array.isArray(array)) return array

	for (let i = 0; i < array.length; i++) {
		transformImageUrlsInObject(array[i], imageFields)
	}

	return array
}
