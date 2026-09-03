import Settings from './Settings'
import Order from './Order'
import Product from './Product'
import HomepageBanners from './HomepageBanners'
const Pages = {
    Settings: Object.assign(Settings, Settings),
Order: Object.assign(Order, Order),
Product: Object.assign(Product, Product),
HomepageBanners: Object.assign(HomepageBanners, HomepageBanners),
}

export default Pages