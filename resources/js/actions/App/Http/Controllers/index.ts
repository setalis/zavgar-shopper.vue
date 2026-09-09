import StripeWebhookController from './StripeWebhookController'
import Shop from './Shop'
import LocaleController from './LocaleController'
import Account from './Account'
import Settings from './Settings'
const Controllers = {
    StripeWebhookController: Object.assign(StripeWebhookController, StripeWebhookController),
Shop: Object.assign(Shop, Shop),
LocaleController: Object.assign(LocaleController, LocaleController),
Account: Object.assign(Account, Account),
Settings: Object.assign(Settings, Settings),
}

export default Controllers