const path = require('path');

module.exports = {
    mode: 'development',
    entry: {
        'cnrt-pricing-page': [
            './admin/js/src/cnrt-pricing-page.js',
        ]
    },
    module: {
        rules: [
            {
                test: /\.(js)$/,
                exclude: /node_modules/,
                use: ['babel-loader']
            }
        ]
    },
    output: {
        filename: '[name].js',
        path: path.resolve('admin/js/')
    },
};