# Hustle
There are two versions of Hustle: Hustle Free (wordpress-popup on the .org repository) and Hustle Pro on wpmudev.

## Development:
1. Clone this repo into your local server's plugins folder: `
git clone git@bitbucket.org:incsub/hustle
`
2. Install submodules: `
git submodule init && git submodule update
`
3. Build scripts: `
gulp
`

For any new logic added to Hustle, make sure to add lots of comments so other devs can quickly navigate around code. This is especially important in quickly finding major bugs.

## Testing Instructions:
As with all plugins, beta testing must be done before sending a beta to QA.
[Layer 2 Testing](https://app.asana.com/0/386439925449855/387485386330175)

## Packaging Instructions:
1) Update plugin version _(include beta version)_ in the following files:
```
opt-in.php
changelog.txt
package.json
```

2) In `Gulpfile.js` update `project version` _(include beta version)_:
```
var hustle = {
	pro: '3.0.3',
	free: '6.0.3',
}
```

3) Build scripts `gulp`

4) Regenerate language files `gulp makepot`

5) Update repo **submodules**
```
git submodule init && git submodule update
```

6) Generate **Hustle Free**:
```
# Removes previous free version then generates a new one.
rm -rf ../wordpress-popup/ && gulp generate-hustle-free
```

7) Pack **Hustle Free**:
```
gulp zip-hustle-free
```

8) Pack **Hustle Pro**:
```
gulp zip-hustle-pro
```

9) **IMPORTANT:** Before releasing make sure to **remove beta version** and re-pack. Give a final test before releasing to make sure zip files are fine.

## To Have In Mind (Before Release):
1. Go all over steps on **Packaging Instructions** and pack beta version, for example: `1.0.1-BETA1`
2. If didn't pass, fix issues.
3. Go all over steps on **Packaging Instructions** and pack new beta `1.0.1-BETA2`
4. If passed, pack final version `1.0.1` _(notice it doesn't include beta number)_
5. Test before releasing to make sure zip files load fine and work.