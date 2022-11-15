const path = require('path');

var config = {
    module: {},
};

var pricing    = Object.assign({}, config, {
    mode: 'production',
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
});


module.exports   = [pricing];