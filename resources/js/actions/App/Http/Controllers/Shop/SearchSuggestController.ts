import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
const SearchSuggestController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SearchSuggestController.url(options),
    method: 'get',
})

SearchSuggestController.definition = {
    methods: ["get","head"],
    url: '/search/suggest',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
SearchSuggestController.url = (options?: RouteQueryOptions) => {
    return SearchSuggestController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
SearchSuggestController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SearchSuggestController.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
SearchSuggestController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: SearchSuggestController.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
    const SearchSuggestControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: SearchSuggestController.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
        SearchSuggestControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: SearchSuggestController.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
        SearchSuggestControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: SearchSuggestController.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    SearchSuggestController.form = SearchSuggestControllerForm
export default SearchSuggestController