// Function to detect if running on Android emulator
function isAndroidEmulator() {
  // Check if we're in a uni-app environment and running on Android
  if (typeof uni !== 'undefined') {
    try {
      const systemInfo = uni.getSystemInfoSync();
      // Check if platform is Android
      if (systemInfo.platform && systemInfo.platform.toLowerCase() === 'android') {
        return true;
      }
    } catch (e) {
      // If getSystemInfoSync fails, we're probably not in a real device/emulator
      console.log('getSystemInfoSync failed:', e);
    }
  }
  return false;
}

module.exports = {
	base_url:process.env.NODE_ENV === 'development' ? (isAndroidEmulator() ? 'https://hongbiennhanh.xyz/api/v1/' : 'https://hongbiennhanh.xyz/api/v1/') : 'http://qting-api-nginx/api/v1/',
	image_base:process.env.NODE_ENV === 'development' ? (isAndroidEmulator() ? 'https://hongbiennhanh.xyz/api/v1/' : 'https://hongbiennhanh.xyz/api/v1/') : 'http://qingtingcun.youyacao.com/'
}
