const amplitude = require('amplitude-js');

export default {
  install: (Vue: any, { apiKey, userId, config }: {apiKey: string, userId: string | number, config: any}) => {
    amplitude.getInstance().init(apiKey, userId, config);

    // eslint-disable-next-line
    Vue.prototype.$amplitude = amplitude;
  },
};