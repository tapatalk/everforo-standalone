import re

from fake_useragent import UserAgent
from flask import Flask, request
from flask_cors import CORS, cross_origin
from linkpreview import link_preview # Link, LinkPreview, LinkGrabber
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium import webdriver

app = Flask(__name__)
CORS(app)

def set_chrome_options() -> Options:
    """Sets chrome options for Selenium.
    Chrome options for headless browser is enabled.
    """
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")

    chrome_options.add_argument('--incognito')
    chrome_options.add_argument('--disable-extensions')
    chrome_options.add_argument('start-maximized')
    chrome_options.add_argument('disable-infobars')

    ua = UserAgent(use_cache_server=False, fallback='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36')
    userAgent = ua.random

    chrome_options.add_argument(f'user-agent={userAgent}')

    chrome_prefs = {}
    chrome_options.experimental_options["prefs"] = chrome_prefs
    chrome_prefs["profile.default_content_settings"] = {"images": 2}
    return chrome_options

@app.route("/apiflask/link_preview", methods=['POST'])
def linkPreview():
    url = request.form.get('url')

    result = {
        "image": '',
        "title": '',
        "description": '',
    }

    if not url:
        return result

    if url.find('amazon.com') != -1:
        url = url[:url.find('?')]

        # m = re.search(r'(\/dp\/[\w\d]+\/)', url)
        # if m and m.group(0):
        #     url = 'https://www.amazon.com' + m.group(0)

    try:
        preview = link_preview(url)

        result['title'] = preview.title if preview.title else preview.force_title
        result['image'] = preview.image if preview.image else preview.absolute_image
        result['description'] = preview.description

    except:
        pass

    if result['title'] and result['image']:
        return result

    try:        
        driver = webdriver.Chrome(options=set_chrome_options())

        driver.get(url)
        driver.implicitly_wait(2)

        images = driver.find_elements_by_css_selector('img')

        for img in images:

            if img.is_displayed():

                w = int(img.get_property('width'))
                h = int(img.get_property('height'))
                if w * h >= 40000:
                    result['image'] = img.get_attribute('src')
                    break

        if not result['image']:
            icon = driver.find_element_by_css_selector("link[type='image/x-icon']")
            result['image'] = icon.get_attribute('href')
    except:
        app.logger.error('can not find image {0}'.format(url))
        
    try:
        desc = driver.find_element_by_css_selector('meta[name="description"]')

        result['description'] = desc.get_attribute('content')
    except:
        app.logger.error('can not find meta[name="description"] {0}'.format(url))

    try:
        if not result['description']:
            desc = driver.find_element_by_css_selector('meta[property="og:description"]')

            if desc:
                result['description'] = desc.get_attribute('content')
    except:
        app.logger.error('can not find meta[property="og:description"] {0}'.format(url))

    try:
        result['title'] = driver.title

        if not result['title']:
            title = driver.find_element_by_css_selector('meta[property="og:title"]')

            if title:
                result['title'] = title.get_attribute('content')
    except:
        app.logger.error('can not find title {0}'.format(url))

    driver.close()

    return result

if __name__ == "__main__":
    # Only for debugging while developing
    app.run(host='0.0.0.0', debug=False)
