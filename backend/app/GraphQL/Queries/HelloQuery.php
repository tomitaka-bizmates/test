<?php

namespace App\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;

class HelloQuery
{
    /**
     * "hello" クエリのリゾルバー
     *
     * @param  null  $root
     * @param  array<string, mixed>  $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo
     * @return string
     */
    public function resolve($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): string
    {
        return 'Hello World！';
    }
}
