export default {
  routes: [
    {
      path: '/',
      name: 'index',
      component: () => import('@/view/home/index'),
        meta : {no_auth : true}
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/view/home/login'),
        meta : {no_auth : true}
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/view/home/register'),
        meta : {no_auth : true}
    },

      /*后台相关路由*/
      {
          path : '/admin/login',
          name : 'adminLogin',
          component : () => import('@/view/admin/login'),
          meta : {no_auth : true}
      },
    {
        path : '/admin',
        name : 'admin',
        component : () => import('@/view/admin/index'),
        children : [
            {
                path : 'article',
                name : 'article',
                component : () => import('@/view/admin/article'),
            },
            {
                path : 'article/edit/:id',
                name : 'articleDetail',
                component : () => import('@/view/admin/articleDetail'),
            },
            {
                path : 'article/create',
                name : 'articleCreate',
                component : () => import('@/view/admin/articleDetail'),
            },
            {
                path : 'tag',
                name : 'tags',
                component : () => import('@/view/admin/tags'),
            },
            {
                path : 'tag/create',
                name : 'tagCreate',
                component : () => import('@/view/admin/tagDetail'),
            },
            {
                path : 'tag/detail',
                name : 'tagDetail',
                component : () => import('@/view/admin/tagDetail'),
            },
            {
                path : 'links',
                name : 'links',
                component : () => import('@/view/admin/links'),
            },
            {
                path : 'adverts',
                name : 'adverts',
                component : () => import('@/view/admin/adverts'),
            },
            {
                path : 'adverts/create',
                name : 'advertCreate',
                component : () => import('@/view/admin/advertDetail'),
            },
            {
                path : 'adverts/detail',
                name : 'advertDetail',
                component : () => import('@/view/admin/advertDetail'),
            }
        ]
    }
  ]
}
