const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    module: {
        rules: [
            ...defaultConfig.module.rules,
            {
                test: /\.(js|jsx)$/, // Ensure both JS and JSX files are handled
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [ '@babel/preset-react' ] // Ensure Babel handles JSX syntax
                    },
                },
            },
        ],
    },
    resolve: {
        extensions: [ '.js', '.jsx' ], // Make sure Webpack resolves JSX files
    },
};
