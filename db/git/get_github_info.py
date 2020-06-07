import requests
from lxml import html
import json
import sys

global branch
branch = "master"

def getBranch(github):
	req = requests.get(github)
	htmlTree = html.fromstring(req.text)
	branch = htmlTree.xpath("//*[@id=\"branch-select-menu\"]/summary/span[1]/text()")[0]
	return branch
	
def getReadme(github, branch):
	try:
		req = requests.get(github)
		htmlTree = html.fromstring(req.text)
		rdme = htmlTree.xpath("//*[@id=\"readme\"]/div[1]/h2/text()")
		rdme = rdme[1].replace("\n","").replace(" ","")
		github = github.replace("github.com","raw.githubusercontent.com")
		return github + "/"+branch+"/"+rdme
	except:
		return "None"


def getRepoName(github):
	lst = github.split("/")
	return lst[4]

def getRepoOwner(github):
	lst = github.split("/")
	return lst[3]

def getIcon0(github, branch):
	repoName = getRepoName(github)
	repoOwner = getRepoOwner(github)
	url = "https://github.com/search?o=asc&q=repo:"+repoOwner+"/"+repoName+"+extension:.png+filename:icon0+fork:true&s=indexed"
	req = requests.get(url)
	htmlTree = html.fromstring(req.text)
	searchResults = []
	elm = htmlTree.xpath('//*[@id="code_search_results"]/div/div') 
	pageCountElement = htmlTree.xpath('//*[@id="code_search_results"]/div[2]/div/em/@data-total-pages')

	if len(elm) == 0: #when no results found, search for all .png instead.
		url = "https://github.com/search?o=asc&q=repo:"+repoOwner+"/"+repoName+"+extension:.png+fork:true&s=indexed"
		req = requests.get(url)
		htmlTree = html.fromstring(req.text)
		searchResults = []
		elm = htmlTree.xpath('//*[@id="code_search_results"]/div/div') 
		pageCountElement = htmlTree.xpath('//*[@id="code_search_results"]/div[2]/div/em/@data-total-pages')

	oUrl = url
	if len(pageCountElement) != 0:
		totalPages = int(pageCountElement[0],10)
		if totalPages > 1:
			for i in range(2,totalPages+1):
				url = oUrl + "&p="+str(i)
				req = requests.get(url)
				htmlTree = html.fromstring(req.text)
				elm.extend(htmlTree.xpath('//*[@id="code_search_results"]/div/div'))
	for element in elm:
		icon0Element = element.xpath('div/div[2]/a/@href')
		
		if len(icon0Element) == 0:
			continue
		
		icon0Url = icon0Element[0]
		
		lst = icon0Url.split('/')
		url = "https://raw.githubusercontent.com/" + lst[1] + "/" +lst[2]+"/"+branch
		
		if len(lst) > 5:
			numpath = len(lst)-5
			for i in range(0,numpath):
				url += "/"+lst[5+i]
			searchResults.append(url)

	if len(searchResults) == 0:
		searchResults = "None"
	return searchResults

def getLatestReleases(github):
	try:
		git_latest = github + "/releases/latest"
		req = requests.get(git_latest)

		htmlTree = html.fromstring(req.text)
		releases = []
		
		xpaths = ['//*[@class="details-reset Details-element border-top pt-3 mt-4 mb-2 mb-md-4"]/div/div/div',"/html/body/div[4]/div/main/div[2]/div/div[2]/div/div[2]/details/div/div/div","/html/body/div[4]/div/main/div[2]/div/div[2]/div/div/div[2]/details/div/div/div","/html/body/div[4]/div/main/div[3]/div/div[2]/div/div[2]/details/div/div/div"] 
		i = 0
		while len(releases) <= 2:
			if i > len(xpaths):
				break
			releases = htmlTree.xpath(xpaths[i])
			i += 1
		
		git_releases = []

		for elm in releases:
			release = elm.xpath("a/@href")[0]
			if release.endswith(".vpk") or release.endswith(".suprx") or release.endswith(".skprx"):
				git_releases.append("https://github.com"+release)
		if len(git_releases) == 0:
			git_releases = "None"
		return git_releases
	except:
		return "None"
		
def getActuralRepo(github):
	github = requests.get(github).url
	repoOwner = getRepoOwner(github)
	repoName = getRepoName(github)
	git_url = "https://github.com/"+repoOwner+"/"+repoName
	return git_url
	
def checkIsGithub(url):
	if url.__contains__("../"):
		return False
	if url.startswith("https://github.com"):
		return True
	else:
		return False

try:
	github_url = sys.argv[1]
except:
	print("not a github url.")

if checkIsGithub(github_url):
	github_url = getActuralRepo(github_url)
	branch = getBranch(github_url)
	repoOwner = getRepoOwner(github_url)
	repoName = getRepoName(github_url)
	releases = getLatestReleases(github_url)
	readme = getReadme(github_url,branch)
	icon0 = getIcon0(github_url,branch)
	dict = {"credits":repoOwner,"name":repoName,"latest_releases":releases,	"readme_md":readme, "icons":icon0,"git_uri":github_url+".git"}
	print(json.dumps(dict))
else:
	print("not a github url")
	