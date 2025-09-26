<!DOCTYPE html>
<html lang="en">
<head>
  
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{$meta['title']}}</title>
  <meta name="description" content="{{$meta['description']}}" />
  <meta name="keywords" content="{{$meta['keywords']}}" />
  <meta name="author" content="{{$meta['site_name']}}" />

  <link rel="canonical" href="{{$meta['canonical']}}" />

  <!-- Open Graph / Facebook Meta Tags -->
  <meta property="og:type" content="website" />
  <meta property="og:title" content="{{$meta['title']}}" />
  <meta property="og:description" content="{{$meta['description']}}" />
  <meta property="og:url" content="{{$meta['url']}}" />
  <meta property="og:image" content="{{asset($meta['image'])}}" />
  <meta property="og:site_name" content="{{$meta['site_name']}}" />

  <!-- Twitter Card Meta Tags -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{$meta['title']}}" />
  <meta name="twitter:description" content="{{$meta['description']}}" />
  <meta name="twitter:image" content="{{asset($meta['image'])}}" />
  <meta name="twitter:site" content="@{{$meta['site_name']}}" />
  <meta name="twitter:creator" content="{{$meta['site_name']}}" />

  <!-- Favicon & Manifest (Optional but recommended) -->
  <link rel="icon" href="/favicon.ico" />
  <link rel="manifest" href="/site.webmanifest" />
  <meta name="theme-color" content="#ffffff" />

  <!-- Optional: Enhanced Google Bot Controls -->
  <meta name="googlebot" content="max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
</head>
<body>
  @if($meta['template'] == 'blank')
    
  @elseif($meta['template'] == 'lorem')

  @elseif($meta['template'] == 'videy')


  @endif
</body>
<script type="text/javascript">
       function setCurrent(newPath) {
      const state = { page: newPath };
      const title = ''; 
      const newUrl = window.location.origin + newPath;
      window.history.pushState(state, title, newUrl);
      const canonical = document.querySelector("link[rel='canonical']");
      if (canonical) canonical.setAttribute('href', newUrl);
      const ogUrl = document.querySelector("meta[property='og:url']");
      if (ogUrl) ogUrl.setAttribute('content', newUrl);
    }
</script>
</html>
