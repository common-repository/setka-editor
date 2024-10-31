import AssetsLoadError from './errors/AssetsLoadError';
import compareVersions from 'compare-versions';

const { select } = wp.data;

const toolbarsDimensions = [
    {
        version: '5.0',
        top: {
            gutenbergBar: 56,
            wpBar: 32,
        },
        bottom: {
            gutenbergBar: 25,
        },
    },
    {
        version: '5.4',
        top: {
            gutenbergBar: 57,
            wpBar: 32,
        },
        bottom: {
            gutenbergBar: 25,
        },
    },
    {
        version: '5.5',
        top: {
            gutenbergBar: 61,
            wpBar: 32,
        },
        bottom: {
            gutenbergBar: 25,
        },
        scrollingElement: '.interface-interface-skeleton__content',
    },
];

/**
 * Setka Editor assets.
 */
export default class EditorAssets {

    /**
     * @type {Object}
     */
    assets;

    /**
     * @type {Object}
     */
    config;

    /**
     * @type {boolean}
     */
    assetsStatus = false;

    /**
     * @type {Promise}
     */
    assetsPromise;

    /**
     * @type {Error}
     */
    assetsError;

    /**
     * @type {Object}
     */
    toolbarsDimensions;

    /**
     * @param {Object} config
     * @param {string} currentVersion
     */
    constructor(config, currentVersion) {
        this.config = { ...config };

        try {
            for(let i = toolbarsDimensions.length - 1; i >= 0; i--) {
                if(compareVersions.compare(toolbarsDimensions[i].version, currentVersion, '<=')) {
                    this.toolbarsDimensions = toolbarsDimensions[i];
                    break;
                }
            }
        } catch (error) {
            this.toolbarsDimensions = toolbarsDimensions[toolbarsDimensions.length - 1];
        }
    }

    /**
     * Initializing loading JSON config for Setka Editor.
     *
     * @return {Promise}
     */
    startLoading(url) {
        return new Promise((resolve, reject) => {
            fetch(url)
                .then(response => {
                    if(200 === response.status) {
                        return response.json();
                    } else {
                        this.assetsError = AssetsLoadError.create();
                        reject(this.assetsError);
                    }
                })
                .then(fetchedConfig => {
                    this.config = { ...this.config, ...fetchedConfig.config };
                    this.assets = { ...fetchedConfig.assets };
                    this.assetsStatus = true;
                    resolve(fetchedConfig);
                })
                .catch(exception => {
                    this.assetsError = AssetsLoadError.create(exception);
                    reject(this.assetsError);
                })
        });
    }

    /**
     * @return {object}
     */
    getConfig() {
        let config = this.config;

        if('string' === typeof this.toolbarsDimensions.scrollingElement) {
            config.scrollingElement = this.toolbarsDimensions.scrollingElement;
        }

        config.headerTopOffset = this.getHeaderTopOffset();
        config.footerBottomOffset = this.getFooterBottomOffset();

        return config;
    }

    /**
     * @return {int}
     */
    getHeaderTopOffset() {
        let offset = this.toolbarsDimensions.top.gutenbergBar;
        if (!select('core/edit-post').isFeatureActive('fullscreenMode')) {
            offset += this.toolbarsDimensions.top.wpBar;
        }
        return offset;
    }

    /**
     * @return {int}
     */
    getFooterBottomOffset() {
        return this.toolbarsDimensions.bottom.gutenbergBar;
    }

    /**
     * Sets assets promise.
     *
     * @param promise {Promise} A promise which indicates the state of assets loading.
     *
     * @return {this} For chain calls.
     */
    setAssetsPromise(promise) {
        this.assetsPromise = promise;
        return this;
    }

    /**
     * @return {Error}
     */
    getAssetsError() {
        return this.assetsError;
    }
}
