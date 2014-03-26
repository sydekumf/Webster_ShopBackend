#!/bin/bash

# delete current index
curl -XDELETE 'http://localhost:9200/shop'

# set up index and mapping
curl -XPOST 'http://localhost:9200/shop' -d @index.json
curl -XPOST 'http://localhost:9200/shop/category/_mapping' -d @categories/mapping.json
curl -XPOST 'http://localhost:9200/shop/product/_mapping' -d @products/mapping.json

# set up products
curl -XPOST 'http://localhost:9200/shop/product/kG5aT9yATh6fDno8zX6k3A' -d @products/product1.json
curl -XPOST 'http://localhost:9200/shop/product/vv4TQOItS2C-nImSmCVL1g' -d @products/product2.json
curl -XPOST 'http://localhost:9200/shop/product/vPkOcM8iRIiIWswgfFxjrA' -d @products/product3.json
curl -XPOST 'http://localhost:9200/shop/product/aFHd3glmR66zCXTewlLL1g' -d @products/product4.json
curl -XPOST 'http://localhost:9200/shop/product/j2y1lqQ-TbO_aGHywpuu9A' -d @products/product5.json
curl -XPOST 'http://localhost:9200/shop/product/qsIMbJYdSHSgZhferndHwQ' -d @products/product6.json
curl -XPOST 'http://localhost:9200/shop/product/HP9AYLL2Svmi3tfiCbkEHg' -d @products/product7.json
curl -XPOST 'http://localhost:9200/shop/product/-NHf3RUbQVy4-hQaGdwjWg' -d @products/product8.json

# set up categories
curl -XPOST 'http://localhost:9200/shop/category' -d @categories/category1.json
curl -XPOST 'http://localhost:9200/shop/category' -d @categories/category2.json
curl -XPOST 'http://localhost:9200/shop/category' -d @categories/category3.json
curl -XPOST 'http://localhost:9200/shop/category' -d @categories/category4.json