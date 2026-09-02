import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
export const suggest = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suggest.url(options),
    method: 'get',
})

suggest.definition = {
    methods: ["get","head"],
    url: '/search/suggest',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
suggest.url = (options?: RouteQueryOptions) => {
    return suggest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
suggest.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suggest.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
suggest.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: suggest.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
    const suggestForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: suggest.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
        suggestForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suggest.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\SearchSuggestController::__invoke
 * @see app/Http/Controllers/Shop/SearchSuggestController.php:14
 * @route '/search/suggest'
 */
        suggestForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suggest.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    suggest.form = suggestForm
const search = {
    suggest: Object.assign(suggest, suggest),
}

export default search