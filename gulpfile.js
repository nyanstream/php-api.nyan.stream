'use strict'

let
	project =     require('./package.json'),
	fs =          require('fs'),
	gulp =        require('gulp'),
	tube =        require('gulp-pipe'),
	bom =         require('gulp-bom'),
	rename =      require('gulp-rename'),
	watch =       require('gulp-watch'),
	plumber =     require('gulp-plumber'),
	cleanCSS =    require('gulp-clean-css'),
	pug =         require('gulp-pug'),
	parseYAML =   require('js-yaml'),
	gulpPHP =     require('gulp-connect-php'),
	liveServer =  require('browser-sync')

let sass = {
	compile:  require('gulp-sass'),
	watch:    require('gulp-watch-sass'),
	vars:     require('gulp-sass-vars')
}

let uglify = {
	core:      require('uglify-es'),
	composer:  require('gulp-uglify/composer')
}

let
	minifyJS = uglify.composer(uglify.core, console),
	reloadServer = () => liveServer.stream()

let parseYAMLfile = fileName => parseYAML.load(fs.readFileSync(`./${fileName}.yaml`, 'utf8'))

let config = parseYAMLfile('project-config')

let vendors = parseYAMLfile('project-vendors')

let dirs = config.dirs

let paths = {
	panel: {
		dev: [`${dirs.dev}/pug/**/*.pug`, `!${dirs.dev}/pug/inc/**/*.pug`],
		prod: dirs.prod.build
	},

	js: {
		dev: `${dirs.dev}/js/**/*.js`,
		prod: `${dirs.prod.build}/${dirs.prod.assets}/js/`,
		kamina: 'node_modules/kamina-js/dist/kamina.min.js',
	},

	css: {
		dev: `${dirs.dev}/scss/**/*.scss`,
		prod: `${dirs.prod.build}/${dirs.prod.assets}/css/`
	}
}

gulp.task('local-php-server', () => {
	gulpPHP.server({
		port: '13378',
		stdio: 'ignore',
		base: dirs.prod.build
	}, () => liveServer({
		proxy: '127.0.0.1:13378',
		notify: false,
		port: 8081
	}))
})

gulp.task('pug', () => tube([
	watch(paths.panel.dev, { ignoreInitial: false }),
	plumber(),
	pug({ locals: {
		VERSION: project.version,
		PATHS: {
			js:   `/${dirs.prod.assets}/js`,
			css:  `/${dirs.prod.assets}/css`,
			img:  `/${dirs.prod.assets}/img`
		},
		LIBS: vendors
	}}),
	bom(),
	rename(file => {
		switch (file.basename) {
			case 'panel-index':
				file.basename = '🤔-panel'
				file.extname = '.php'
		}
	}),
	gulp.dest(paths.panel.prod),
	reloadServer()
]))

gulp.task('get-kamina', () => tube([
	gulp.src(paths.js.kamina),
	bom(),
	gulp.dest(paths.js.prod)
]))

gulp.task('minify-js', () => tube([
	watch(paths.js.dev, { ignoreInitial: false }),
	plumber(),
	minifyJS({}),
	bom(),
	rename({suffix: '.min'}),
	gulp.dest(paths.js.prod),
	reloadServer()
]))

let scssTubes = [
	plumber(),
	sass.vars({
		VERSION: project.version
	}, { verbose: false }),
	sass.compile({ outputStyle: 'compressed' }),
	cleanCSS(),
	bom(),
	rename({suffix: '.min'}),
	gulp.dest(paths.css.prod)
]

gulp.task('scss:only-compile', () => tube(
	[gulp.src(paths.css.dev)].concat(scssTubes)
))

gulp.task('scss:dev', () => tube(
	[sass.watch(paths.css.dev)].concat(scssTubes, [reloadServer()])
))

gulp.task('default', ['pug', 'get-kamina', 'minify-js', 'scss:dev'])
gulp.task('dev', ['local-php-server', 'default'])
