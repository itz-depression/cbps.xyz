import requests
from lxml import html
import sys

LOGIN_USERNAME = "GITHUB_USERNAME"
LOGIN_PASSWORD = "GITHUB_PASSWORD"

REPO_OWNER = "KuromeSan"
MERGE_WITH_MASTER = sys.argv[1]
MASTER_BRANCH = "master"

header = {"User-Agent":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36;",
		"Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
		"Accept-Encoding":"gzip, deflate, br",
		"Accept-Language":"en-GB,en;q=0.9,ja;q=0.8"}

githubSession = requests.Session()

loginPageRequest = githubSession.get("https://github.com/login",headers=header);

htmlTree = html.fromstring(loginPageRequest.text)
timestamp = htmlTree.xpath('//*[@id="login"]/form/div[4]/input[7]')[0].get('value')
timestamp_secret = htmlTree.xpath('//*[@id="login"]/form/div[4]/input[8]')[0].get('value')
authenticity_token = htmlTree.xpath('//*[@id="login"]/form/input[1]')[0].get('value')
required_feild = htmlTree.xpath('//*[@id="login"]/form/div[4]/input[6]')[0].get('name')

#print(timestamp)
#print(timestamp_secret)
#print(authenticity_token)
#print(required_feild)

postdata = {"commit":"Sign+In",
			"authenticity_token":authenticity_token,
			"ga_id":"",
			"login":LOGIN_USERNAME,
			"password":LOGIN_PASSWORD,
			"webauthn-support":"supported",
			"webauthn-iuvpaa-support":"unsupported",
			"return_to":"",
			required_feild:"",
			"timestamp":timestamp,
			"timestamp_secret":timestamp_secret}


postreq = githubSession.post("https://github.com/session",headers=header,data=postdata)
#print(postreq.status_code)

pullRequestPageRequest = githubSession.get('https://github.com/KuromeSan/cbps-db/compare/'+MASTER_BRANCH+'...'+MERGE_WITH_MASTER,headers=header)
##print(pullRequestPageRequest.status_code)

htmlTree = html.fromstring(pullRequestPageRequest.text)
authenticity_token = htmlTree.xpath('//*[@id="new_pull_request"]/input[1]')[0].get('value')
timestamp = htmlTree.xpath('//*[@id="new_pull_request"]/div/div[1]/div/div[1]/input[2]')[0].get('value')
timestamp_secret = htmlTree.xpath('//*[@id="new_pull_request"]/div/div[1]/div/div[1]/input[3]')[0].get('value')
required_feild = htmlTree.xpath('//*[@id="new_pull_request"]/div/div[1]/div/div[1]/input[1]')[0].get('name')

#print(timestamp)
#print(timestamp_secret)
#print(authenticity_token)
#print(required_feild)

postdata = {"authenticity_token":authenticity_token,
			"pull_request[title]":sys.argv[2],
			"saved_reply_id":"",
			"pull_request[body]":sys.argv[3],
			"path":"",
			"line":"",
			"start_line":"",
			"preview_side":"",
			"preview_start_side":"",
			"start_commit_oid":"",
			"end_commit_oid":"",
			"base_commit_oid":"",
			"comment_id":"",
			required_feild:"",
			"timestamp":timestamp,
			"timestamp_secret":timestamp_secret,
			"draft":"off",
			"quick_pull":""}

pullRequestRequest = githubSession.post('https://github.com/KuromeSan/cbps-db/pull/create?base='+REPO_OWNER+':'+MASTER_BRANCH+'&head='+REPO_OWNER+':'+MERGE_WITH_MASTER,data=postdata,headers=header)

#print(pullRequestRequest.status_code)

githubHomePageRequest = githubSession.get("http://github.com",headers=header);
#print(githubHomePageRequest.status_code)

htmlTree = html.fromstring(githubHomePageRequest.text)
authenticity_token = htmlTree.xpath('/html/body/div[1]/header/div[7]/details/details-menu/form/input[1]')[0].get('value')
timestamp = htmlTree.xpath('/html/body/div[1]/header/div[7]/details/details-menu/form/input[3]')[0].get('value')
timestamp_secret = htmlTree.xpath('/html/body/div[1]/header/div[7]/details/details-menu/form/input[4]')[0].get('value')
required_feild = htmlTree.xpath('/html/body/div[1]/header/div[7]/details/details-menu/form/input[2]')[0].get('name')

#print(timestamp)
#print(timestamp_secret)
#print(authenticity_token)
#print(required_feild)

postdata = {"authenticity_token":authenticity_token,
			required_feild:"",
			"timestamp":timestamp,
			"timestamp_secret":timestamp_secret}

logoutRequest = githubSession.get("https://github.com/logout",headers=header,data=postdata)
#print(logoutRequest.status_code)

print(pullRequestRequest.url)