var atwhoOptions, previewUrl;

atwhoOptions = {
  at: "!",
  tpl: '<li class="lttm" data-value="![${alt}](${imageUrl})"><img src="${imagePreviewUrl}" /></li>',
  limit: 80,
  display_timeout: 1000,
  search_key: null,
  callbacks: {
    matcher: function(flag, subtext) {
      var match, regexp;
      regexp = new XRegExp("(\\s+|^)" + flag + "([\\p{L}_-]+)$", "gi");
      match = regexp.exec(subtext);
      if (!(match && match.length >= 2)) {
        return null;
      }
      return match[2];
    },
    remote_filter: function(query, callback) {
      var kind;
      if (!query) {
        return;
      }
      kind = query[0].toLowerCase();
      query = query.slice(1);
      switch (false) {
        case kind !== "t":
          return $.getJSON("http://api.tiqav.com/search/random.json", function(data) {
            var images;
            images = [];
            $.each(data, function(idx, val) {
              url = "http://tiqav.com/" + val['id'] + "." + val['ext']
              return images.push({
                name: 'tiqav',
                imageUrl: url,
                imagePreviewUrl: previewUrl(url),
                alt: "LGTM"
              });
            });
            return callback(images);
          });
        case kind !== "b":
          return $.getJSON("http://bjin.me/api/?type=rand&count=15&format=json", function(data) {
            var images;
            images = [];
            $.each(data, function(idx, val) {
              url = "http://bjin.me//images/pic" + val['id'] + ".jpg"
              return images.push({
                name: val['category'],
                imageUrl: add_lgtm_url(url),
                imagePreviewUrl: previewUrl(val['thumb']),
                alt: "LGTM"
              });
            });
            return callback(images);
          });
        case kind !== "c":
          return $.ajax({
            url: "http://thecatapi.com/api/images/get?format=xml&results_per_page=4",
            type: 'GET',
            dataType: 'xml',
            timeout: 3000,
            success: function(xml) {
              var images;
              images = [];
              $(xml).find('url').each(function() {
                url = $(this).text();
                return images.push({
                  name: 'cat',
                  imageUrl: add_lgtm_url(url),
                  imagePreviewUrl: previewUrl(url),
                  alt: "LGTM"
                });
              });
              return callback(images);
            }
          });
      }
    }
  }
};

add_lgtm_url = function(url) {
  return 'http://hisaichilgtm.herokuapp.com/' + url;
};

previewUrl = function(url) {
  var hmac, shaObj;
  if (location.protocol === "http:") {
    return url;
  }
  if (url.indexOf('https:') === 0) {
    return url;
  }
  shaObj = new jsSHA("SHA-1", 'TEXT');
  shaObj.setHMACKey('lttmlttm', 'TEXT');
  shaObj.update(url);
  hmac = shaObj.getHMAC('HEX');
  return "https://lttmcamo.herokuapp.com/" + hmac + "?url=" + url;
};

$(document).on('focusin', function(ev) {
  var $this;
  $this = $(ev.target);
  if (!$this.is('textarea')) {
    return;
  }
  return $this.atwho(atwhoOptions);
});

$(document).on('keyup.atwhoInner', function(ev) {
  return setTimeout(function() {
    var $currentItem, $parent, offset;
    $currentItem = $('.atwho-view .cur');
    if ($currentItem.length === 0) {
      return;
    }
    $parent = $($currentItem.parents('.atwho-view')[0]);
    offset = Math.floor($currentItem.offset().top - $parent.offset().top) - 1;
    if ((offset < 0) || (offset > 250)) {
      return setTimeout(function() {
        var row;
        offset = Math.floor($currentItem.offset().top - $parent.offset().top) - 1;
        row = Math.floor(offset / 150);
        return $parent.scrollTop($parent.scrollTop() + row * 150 - 75);
      }, 100);
    }
  });
});
