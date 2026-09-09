import Settings from './Settings'
import Order from './Order'
import Product from './Product'
import Collection from './Collection'
import HomepageBanners from './HomepageBanners'
import MenuItems from './MenuItems'
const Pages = {
    Settings: Object.assign(Settings, Settings),
Order: Object.assign(Order, Order),
Product: Object.assign(Product, Product),
Collection: Object.assign(Collection, Collection),
HomepageBanners: Object.assign(HomepageBanners, HomepageBanners),
MenuItems: Object.assign(MenuItems, MenuItems),
}

export default Pages