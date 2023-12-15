// Import all necessary Storefront plugins and scss files
import BrandCrockWanderlust from './brand-crock-wander-lust/brand-crock-wander-lust.plugin.js';
document.addEventListener("contextmenu", function (event) {
  event.preventDefault();
});

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('BrandCrockWanderlust', BrandCrockWanderlust, 'body');

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
