(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[5],{112:function(e,t,r){"use strict";var c=r(13),n=r.n(c),l=r(0),a=r(147),o=r(4),i=r.n(o);r(216);const s=e=>({thousandSeparator:null==e?void 0:e.thousandSeparator,decimalSeparator:null==e?void 0:e.decimalSeparator,fixedDecimalScale:!0,prefix:null==e?void 0:e.prefix,suffix:null==e?void 0:e.suffix,isNumericString:!0});t.a=e=>{var t;let{className:r,value:c,currency:o,onValueChange:u,displayType:p="text",...m}=e;const d="string"==typeof c?parseInt(c,10):c;if(!Number.isFinite(d))return null;const b=d/10**o.minorUnit;if(!Number.isFinite(b))return null;const v=i()("wc-block-formatted-money-amount","wc-block-components-formatted-money-amount",r),y=null!==(t=m.decimalScale)&&void 0!==t?t:null==o?void 0:o.minorUnit,f={...m,...s(o),decimalScale:y,value:void 0,currency:void 0,onValueChange:void 0},g=u?e=>{const t=+e.value*10**o.minorUnit;u(t)}:()=>{};return Object(l.createElement)(a.a,n()({className:v,displayType:p},f,{value:b,onValueChange:g}))}},216:function(e,t){},276:function(e,t,r){"use strict";r.d(t,"a",(function(){return c}));var c=function(){return(c=Object.assign||function(e){for(var t,r=1,c=arguments.length;r<c;r++)for(var n in t=arguments[r])Object.prototype.hasOwnProperty.call(t,n)&&(e[n]=t[n]);return e}).apply(this,arguments)};Object.create,Object.create},277:function(e,t,r){"use strict";function c(e){return e.toLowerCase()}r.d(t,"a",(function(){return a}));var n=[/([a-z0-9])([A-Z])/g,/([A-Z])([A-Z][a-z])/g],l=/[^A-Z0-9]+/gi;function a(e,t){void 0===t&&(t={});for(var r=t.splitRegexp,a=void 0===r?n:r,i=t.stripRegexp,s=void 0===i?l:i,u=t.transform,p=void 0===u?c:u,m=t.delimiter,d=void 0===m?" ":m,b=o(o(e,a,"$1\0$2"),s,"\0"),v=0,y=b.length;"\0"===b.charAt(v);)v++;for(;"\0"===b.charAt(y-1);)y--;return b.slice(v,y).split("\0").map(p).join(d)}function o(e,t,r){return t instanceof RegExp?e.replace(t,r):t.reduce((function(e,t){return e.replace(t,r)}),e)}},284:function(e,t,r){"use strict";r.d(t,"a",(function(){return l}));var c=r(276),n=r(277);function l(e,t){return void 0===t&&(t={}),function(e,t){return void 0===t&&(t={}),Object(n.a)(e,Object(c.a)({delimiter:"."},t))}(e,Object(c.a)({delimiter:"-"},t))}},286:function(e,t,r){"use strict";r.d(t,"a",(function(){return m}));var c=r(4),n=r.n(c),l=r(22),a=r(27);const o=e=>Object(a.a)(e)?JSON.parse(e)||{}:Object(l.a)(e)?e:{};var i=r(284),s=r(130);function u(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};const t={};return Object(s.getCSSRules)(e,{selector:""}).forEach(e=>{t[e.key]=e.value}),t}function p(e,t){return e&&t?`has-${Object(i.a)(t)}-${e}`:""}const m=e=>{const t=Object(l.a)(e)?e:{style:{}},r=o(t.style),c=function(e){var t,r,c,a,o,i,s;const{backgroundColor:m,textColor:d,gradient:b,style:v}=e,y=p("background-color",m),f=p("color",d),g=function(e){if(e)return`has-${e}-gradient-background`}(b),O=g||(null==v||null===(t=v.color)||void 0===t?void 0:t.gradient);return{className:n()(f,g,{[y]:!O&&!!y,"has-text-color":d||(null==v||null===(r=v.color)||void 0===r?void 0:r.text),"has-background":m||(null==v||null===(c=v.color)||void 0===c?void 0:c.background)||b||(null==v||null===(a=v.color)||void 0===a?void 0:a.gradient),"has-link-color":Object(l.a)(null==v||null===(o=v.elements)||void 0===o?void 0:o.link)?null==v||null===(i=v.elements)||void 0===i||null===(s=i.link)||void 0===s?void 0:s.color:void 0})||void 0,style:u({color:(null==v?void 0:v.color)||{}})}}({...t,style:r}),i=function(e){var t;const r=(null===(t=e.style)||void 0===t?void 0:t.border)||{};return{className:function(e){var t;const{borderColor:r,style:c}=e,l=r?p("border-color",r):"";return n()({"has-border-color":r||(null==c||null===(t=c.border)||void 0===t?void 0:t.color),borderColorClass:l})}(e)||void 0,style:u({border:r})}}({...t,style:r}),s=function(e){const{style:t}=e;return{className:void 0,style:u({spacing:(null==t?void 0:t.spacing)||{}})}}({...t,style:r}),m=(e=>{const t=o(e.style),r=Object(l.a)(t.typography)?t.typography:{},c=Object(a.a)(r.fontFamily)?r.fontFamily:"";return{className:e.fontFamily?`has-${e.fontFamily}-font-family`:c,style:{fontSize:e.fontSize?`var(--wp--preset--font-size--${e.fontSize})`:r.fontSize,fontStyle:r.fontStyle,fontWeight:r.fontWeight,letterSpacing:r.letterSpacing,lineHeight:r.lineHeight,textDecoration:r.textDecoration,textTransform:r.textTransform}}})(t);return{className:n()(m.className,c.className,i.className,s.className),style:{...m.style,...c.style,...i.style,...s.style}}}},332:function(e,t,r){"use strict";var c=r(0),n=r(1),l=r(112),a=r(4),o=r.n(a),i=r(41);r(333);const s=e=>{let{currency:t,maxPrice:r,minPrice:a,priceClassName:s,priceStyle:u={}}=e;return Object(c.createElement)(c.Fragment,null,Object(c.createElement)("span",{className:"screen-reader-text"},Object(n.sprintf)(
/* translators: %1$s min price, %2$s max price */
Object(n.__)("Price between %1$s and %2$s","woocommerce"),Object(i.formatPrice)(a),Object(i.formatPrice)(r))),Object(c.createElement)("span",{"aria-hidden":!0},Object(c.createElement)(l.a,{className:o()("wc-block-components-product-price__value",s),currency:t,value:a,style:u})," — ",Object(c.createElement)(l.a,{className:o()("wc-block-components-product-price__value",s),currency:t,value:r,style:u})))},u=e=>{let{currency:t,regularPriceClassName:r,regularPriceStyle:a,regularPrice:i,priceClassName:s,priceStyle:u,price:p}=e;return Object(c.createElement)(c.Fragment,null,Object(c.createElement)("span",{className:"screen-reader-text"},Object(n.__)("Previous price:","woocommerce")),Object(c.createElement)(l.a,{currency:t,renderText:e=>Object(c.createElement)("del",{className:o()("wc-block-components-product-price__regular",r),style:a},e),value:i}),Object(c.createElement)("span",{className:"screen-reader-text"},Object(n.__)("Discounted price:","woocommerce")),Object(c.createElement)(l.a,{currency:t,renderText:e=>Object(c.createElement)("ins",{className:o()("wc-block-components-product-price__value","is-discounted",s),style:u},e),value:p}))};t.a=e=>{let{align:t,className:r,currency:n,format:a="<price/>",maxPrice:i,minPrice:p,price:m,priceClassName:d,priceStyle:b,regularPrice:v,regularPriceClassName:y,regularPriceStyle:f,style:g}=e;const O=o()(r,"price","wc-block-components-product-price",{["wc-block-components-product-price--align-"+t]:t});a.includes("<price/>")||(a="<price/>",console.error("Price formats need to include the `<price/>` tag."));const j=v&&m!==v;let N=Object(c.createElement)("span",{className:o()("wc-block-components-product-price__value",d)});return j?N=Object(c.createElement)(u,{currency:n,price:m,priceClassName:d,priceStyle:b,regularPrice:v,regularPriceClassName:y,regularPriceStyle:f}):void 0!==p&&void 0!==i?N=Object(c.createElement)(s,{currency:n,maxPrice:i,minPrice:p,priceClassName:d,priceStyle:b}):m&&(N=Object(c.createElement)(l.a,{className:o()("wc-block-components-product-price__value",d),currency:n,value:m,style:b})),Object(c.createElement)("span",{className:O,style:g},Object(c.createInterpolateElement)(a,{price:N}))}},333:function(e,t){},419:function(e,t,r){"use strict";r.r(t),r.d(t,"Block",(function(){return p}));var c=r(0),n=r(4),l=r.n(n),a=r(332),o=r(41),i=r(60),s=r(286),u=r(144);r(420);const p=e=>{var t,r;const{className:n,textAlign:u,isDescendentOfSingleProductTemplate:p}=e,m=Object(s.a)(e),{parentName:d,parentClassName:b}=Object(i.useInnerBlockLayoutContext)(),{product:v}=Object(i.useProductDataContext)(),y="woocommerce/all-products"===d,f=l()("wc-block-components-product-price",n,m.className,{[b+"__product-price"]:b});if(!v.id&&!p){const e=Object(c.createElement)(a.a,{align:u,className:f});return y?Object(c.createElement)("div",{className:"wp-block-woocommerce-product-price"},e):e}const g=v.prices,O=p?Object(o.getCurrencyFromPriceResponse)():Object(o.getCurrencyFromPriceResponse)(g),j=g.price!==g.regular_price,N=l()({[b+"__product-price__value"]:b,[b+"__product-price__value--on-sale"]:j}),_=Object(c.createElement)(a.a,{align:u,className:f,style:m.style,regularPriceStyle:m.style,priceStyle:m.style,priceClassName:N,currency:O,price:p?"5000":g.price,minPrice:null==g||null===(t=g.price_range)||void 0===t?void 0:t.min_amount,maxPrice:null==g||null===(r=g.price_range)||void 0===r?void 0:r.max_amount,regularPrice:p?"5000":g.regular_price,regularPriceClassName:l()({[b+"__product-price__regular"]:b})});return y?Object(c.createElement)("div",{className:"wp-block-woocommerce-product-price"},_):_};t.default=e=>e.isDescendentOfSingleProductTemplate?Object(c.createElement)(p,e):Object(u.withProductDataContext)(p)(e)},420:function(e,t){}}]);