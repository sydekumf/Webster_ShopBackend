#!/bin/bash

# delete current index
curl -XDELETE 'http://localhost:9200/shop'

# set up index and mapping
curl -XPOST 'http://localhost:9200/shop' -d @index.json
curl -XPOST 'http://localhost:9200/shop/category/_mapping' -d @categories/mapping.json
curl -XPOST 'http://localhost:9200/shop/product/_mapping' -d @products/mapping.json

# set up categories
curl -XPOST 'http://localhost:9200/shop/category/X1pXl3CwSkKXqhK-PBUIcQ' -d @categories/category1.json
curl -XPOST 'http://localhost:9200/shop/category/CsRzTCJYT2ypJwmzBvUS_Q' -d @categories/category2.json
curl -XPOST 'http://localhost:9200/shop/category/E-58dLNASyeUD5WapEv6tw' -d @categories/category3.json
curl -XPOST 'http://localhost:9200/shop/category/gbuTUalpTAazJpMXAju62w' -d @categories/category4.json

# set up products
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product1.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product2.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product3.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product4.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product5.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product6.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product7.json
curl -XPOST 'http://localhost:9200/shop/product' -d @products/product8.json