import HomeController from './HomeController'
import ProductController from './ProductController'
import ProductReviewController from './ProductReviewController'
import CategoryController from './CategoryController'
import CollectionController from './CollectionController'
import BrandController from './BrandController'
import SearchController from './SearchController'
import CartController from './CartController'
import ZoneController from './ZoneController'
import CheckoutController from './CheckoutController'
import StripePaymentController from './StripePaymentController'
import CheckoutSuccessController from './CheckoutSuccessController'
const Shop = {
    HomeController: Object.assign(HomeController, HomeController),
ProductController: Object.assign(ProductController, ProductController),
ProductReviewController: Object.assign(ProductReviewController, ProductReviewController),
CategoryController: Object.assign(CategoryController, CategoryController),
CollectionController: Object.assign(CollectionController, CollectionController),
BrandController: Object.assign(BrandController, BrandController),
SearchController: Object.assign(SearchController, SearchController),
CartController: Object.assign(CartController, CartController),
ZoneController: Object.assign(ZoneController, ZoneController),
CheckoutController: Object.assign(CheckoutController, CheckoutController),
StripePaymentController: Object.assign(StripePaymentController, StripePaymentController),
CheckoutSuccessController: Object.assign(CheckoutSuccessController, CheckoutSuccessController),
}

export default Shop