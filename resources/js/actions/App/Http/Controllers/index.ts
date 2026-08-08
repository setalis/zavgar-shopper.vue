import Shop from './Shop'
import LocaleController from './LocaleController'
import StripeWebhookController from './StripeWebhookController'
import Account from './Account'
import Settings from './Settings'
const Controllers = {
    Shop: Object.assign(Shop, Shop),
LocaleController: Object.assign(LocaleController, LocaleController),
StripeWebhookController: Object.assign(StripeWebhookController, StripeWebhookController),
Account: Object.assign(Account, Account),
Settings: Object.assign(Settings, Settings),
}

export default Controllers