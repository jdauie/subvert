[CloudAE][] development has reached a preview stage, so I am beginning this blog to share my development efforts. I solve a number of problems as I develop applications, and I no longer have the energy to share solutions on forums and message boards. Perhaps I will find the motivation to provide details here as I run benchmarks and resolve issues that interest the community. We shall see.

![](http://blog.jacere.net/wp-content/uploads/2011/12/500x363xapp_2d_toronto.png.pagespeed.ic.Jzuaqi9LdA.png "app_2d_toronto")


[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"

After completing the segmentation, I went ahead and implemented a fast edge detection filter.

[![](http://blog.jacere.net/wp-content/uploads/2011/12/stadium_edges-239x300.png "stadium_edges")](http://blog.jacere.net/wp-content/uploads/2011/12/stadium_edges.png)
[![](http://blog.jacere.net/wp-content/uploads/2011/12/toronto_edges-270x300.png "toronto_edges")](http://blog.jacere.net/wp-content/uploads/2011/12/toronto_edges.png)
[![](http://blog.jacere.net/wp-content/uploads/2011/12/gray1_edges-133x300.png "gray1_edges")](http://blog.jacere.net/wp-content/uploads/2011/12/gray1_edges.png)


Thankfully, they were both [correct][oldnewthing].

[oldnewthing]: http://blogs.msdn.com/b/oldnewthing/archive/2013/05/01/10415282.aspx "I wrote two lines of code yesterday"

I have now added [LASzip][] support to [CloudAE][].  LASzip is a compression library that was developed by [Martin Isenburg][isenburg][^1] for compressing LAS points into an LAZ stream.  Using the LASzip library, an LAZ file can be decompressed transparently as if it was an LAS source.  This differs from the approach taken by [LizardTech][] for the MG4 release of [LiDAR Compressor][lidarcompressor], which does not necessarily maintain conformance to the LAS point types.  Due to the compression efficiency and compatibility of the LAZ format, it has become popular for storing archive tiles in open data services such as [OpenTopography][] and [NLSF][].

I link to the LASzip library in a similar fashion as [libLAS][], while providing a C++/CLI wrapper for operating on blocks of bytes.  As a result, I am able to pretend that the LAZ file is actually an LAS file at the byte level rather than the point level.  This allows me to support the format easily within my Source/Segment/Composite/Enumerator framework.  I merely needed to add a simple LAZ Source and StreamReader, and the magic doth happen.  There is minimal overhead with this approach, since the single extra memcpy for each point is not much compared to decompression time.

LAZ writer support is similarly straightforward, but I am sticking with LAS output for now, until I have more time to determine performance impacts.

[^1]: Thanks to Martin for his suggestions regarding implementation performance.  It turns out there is a bug in the ifstream/streambuf when compiling with CLR support.  I had to extract the stream operations into a fully native class in order to achieve the desired performance.

[laszip]: http://www.laszip.org/  "LASZip"
[liblas]: http://www.liblas.org/ "libLAS"
[isenburg]: http://www.cs.unc.edu/~isenburg/  "Martin Isenburg"
[lizardtech]: http://www.lizardtech.com/  "LizardTech"
[lidarcompressor]: http://www.lizardtech.com/products/lidar/ "LiDAR Compressor"
[opentopography]: http://www.opentopography.org  "OpenTopography"
[nlsf]: https://tiedostopalvelu.maanmittauslaitos.fi/tp/kartta?lang=en  "National Land Survey of Finland"

[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"


The [Matanuska-Susitna Borough's 2011 LiDAR & Imagery Project][matsu] has been around for a while now, but I recently discovered that the [Point MacKenzie][point-mackenzie-laz] data on the public mirror has been made available as compressed LAZ.  The LASzip format is not ideal for all purposes, but it is always a major improvement when data servers provide data in LAZ format because of the massive reduction in bandwidth and download times.

Even when [CloudAE][] did not support LAZ directly, it was always well worth it to download the compressed data and [convert][lastools] it to LAS.  Now that [CloudAE supports LAZ][laz-support], everything is much simpler.


[lastools]: http://www.cs.unc.edu/~isenburg/lastools/ "LAStools"
[matsu]: http://matsu.gina.alaska.edu/ "Matanuska-Susitna Borough's 2011 LiDAR & Imagery Project"
[point-mackenzie-laz]: http://matsu.gina.alaska.edu/LiDAR/Point_MacKenzie/Point_Cloud/Classified.laz/

[cloudae]: http://blog.jacere.net/cloudae/ "CloudAE"
[laz-support]: http://blog.jacere.net/2012/09/laz-support/ "LAZ Support"


In my last post, I referenced the block-based nature of point cloud handling in [CloudAE][].  The following example shows the basic format for enumerating over point clouds using the framework.  At this level, the point source could be a text file, an LAS or LAZ file, or a composite of many individual files of the supported types.  The enumeration hides all such details from the consumer.

	using (var process = progressManager.StartProcess("ChunkProcess"))
	{
		foreach (var chunk in source.GetBlockEnumerator(process))
		{
			byte* pb = chunk.PointDataPtr;
			while (pb < chunk.PointDataEndPtr)
			{
				SQuantizedPoint3D* p = (SQuantizedPoint3D*)pb;
				
				// evaluate point
				
				pb += chunk.PointSizeBytes;
			}
		}
	}

This can be simplified even more by factoring the chunk handling into IChunkProcess instances, which can encapsulate analysis, conversion, or filtering operations.

	var chunkProcesses = new ChunkProcessSet(
		quantizationConverter,
		tileFilter,
		segmentBuffer
	);
	 
	using (var process = progressManager.StartProcess("ChunkProcessSet"))
	{
		foreach (var chunk in source.GetBlockEnumerator(process))
			chunkProcesses.Process(chunk);
	}

The chunk enumerators handle progress reporting and checking for cancellation messages.  In addition, they hide any source implementation details, transparently reading from whatever IStreamReader is implemented for the underlying sequential sources.


[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"


When I started programming as a lad, I initially used INI files to manage configuration settings.  Lightweight and easy to parse, they were a simple way to get started, whether I was rolling my own parsing or, later, using existing parsing utilities.  Soon, however, the allure of the Windows Registry drew me in, and I began using it almost exclusively for configuration settings.  I found the registry convenient for most purposes, and only resorted to INI files for portable applications that I would run from a disk.  This state of affairs lasted almost a decade, until I started to encounter registry permission problems on newer operating systems with improved user security controls.  I finally started adopting some different configuration mechanisms.

The [Application Settings][appsettings] mechanism is the default way to persist and manage application configuration settings in .NET.  For those who prefer to adjust the behavior, it supports [custom persistence implementations][persistence].  This feature allows design-time development of application and user settings, and is improved by adding [ConfigurationValidatorAttribute][validatorattr] constraints.

And now, here I go, flying in the face of convention.  I dislike managing settings outside the scope of the code that will use them, so I have written a `PropertyManager` which uses generics, reflection and delegates to provide a good alternative to the built-in Application Settings.  It allows the declaration of properties at a more reasonable scope, automated discovery, and simple run-time management.

	private static readonly IPropertyState<ByteSizesSmall> PROPERTY_SEGMENT_SIZE;
	private static readonly IPropertyState<bool> PROPERTY_REUSE_TILING;
	 
	static ProcessingSet()
	{
		PROPERTY_SEGMENT_SIZE = Context.RegisterOption(Context.OptionCategory.Tiling, "MaxSegmentSize", ByteSizesSmall.MB_256);
		PROPERTY_REUSE_TILING = Context.RegisterOption(Context.OptionCategory.Tiling, "UseCache", true);
	}

Once the properties have been defined, they can be used easily through the Value property, similar to the usage of a [Nullable<T>][nullable].

	if (PROPERTY_REUSE_TILING.Value)
	{
		// do something exciting
	}

So, what makes this all happen?  Other than some validation, it boils down to the call to `PropertyManager.Create()`.

	public static IPropertyState<T> RegisterOption<T>(OptionCategory category, string name, T defaultValue)
	{
		if (!Enum.IsDefined(typeof(OptionCategory), category))
			throw new ArgumentException("Invalid category.");
	 
		if (string.IsNullOrWhiteSpace(name))
			throw new ArgumentException("Option registration is empty.", "name");
	 
		if (name.IndexOfAny(new[] { '.', '\\', ' ', ':' }) > 0)
			throw new ArgumentException("Option registration contains invalid characters.", "name");
	 
		string categoryName = Enum.GetName(typeof(OptionCategory), category);
		string optionName = String.Format("{0}.{1}", categoryName, name);
	 
		IPropertyState<T> state = null;
		var propertyName = PropertyManager.CreatePropertyName(optionName);
		if (c_registeredProperties.ContainsKey(propertyName))
		{
			state = c_registeredProperties[propertyName] as IPropertyState<T>;
			if (state == null)
				throw new Exception("Duplicate option registration with a different type for {0}.");
	 
			WriteLine("Duplicate option registration: ", propertyName);
		}
		else
		{
			state = PropertyManager.Create(propertyName, defaultValue);
			c_registeredProperties.Add(propertyName, state);
			c_registeredPropertiesList.Add(state);
		}
	 
		return state;
	}

The static property manager contains the necessary methods to create, update and retrieve properties.  It wraps an `IPropertyManager` instance which knows the details about persistence and conversion for the storage mode that it represents.  I have standard implementations for the Registry, XML, and a web service.

	public interface IPropertyManager
	{
		PropertyName CreatePropertyName(string name);
		PropertyName CreatePropertyName(string prefix, string name);
		IPropertyState<T> Create<T>(PropertyName name, T defaultValue);
		bool SetProperty(PropertyName name, ISerializeStateBinary value);
		bool GetProperty(PropertyName name, ISerializeStateBinary value);
		bool SetProperty(IPropertyState state);
		bool GetProperty(IPropertyState state);
	}

As for data binding, just create a `DataGrid` with a `TwoWay` binding on `Value`, and we have ourselves a property editor.

	dataGrid.ItemsSource = Context.RegisteredProperties;

[![alt][options-img]](http://blog.jacere.net/wp-content/uploads/2012/10/options.png)

The main downside with this approach to application and user settings is that configuration validators cannot be used as attributes on the Value property of the `IPropertyState<T>`.  The workaround for this is validation delegates which work just as well, but are not quite as nice visually.


[appsettings]: http://msdn.microsoft.com/en-us/library/k4s6c3a0.aspx "Application Settings"
[persistence]: http://msdn.microsoft.com/en-us/library/ms973902.aspx  "Persisting Application Settings in the .NET Framework"
[validatorattr]: http://msdn.microsoft.com/en-us/library/system.configuration.configurationvalidatorattribute.aspx  "ConfigurationValidatorAttribute Class"
[nullable]: http://msdn.microsoft.com/en-us/library/b3h38hb0.aspx  "Nullable&lt;T&gt; Structure"

[options]: http://blog.jacere.net/wp-content/uploads/2012/10/options.png

[options-img]: http://blog.jacere.net/wp-content/uploads/2012/10/462x119xoptions_edit.png.pagespeed.ic.0x_s0BlA-d.png  "options"


I haven't posted about development for months, but I have been getting some work done.  My current project is a new templating engine that I am preliminarily calling templ@te.  I finalized the first draft of the template language recently and just got around to implementing a parser/evaluator in PHP.

So, why on earth would I create another templating language?  There are plenty of options out there like [Twig][], [Smarty][], [Jinja2][], [Cheetah][], [Genshi][], [Django][], [Mako][], [Myghty][], [ctemplate][], and many more.  The short answer, as with most of my projects, is that I work on whatever grabs my interest.  The long answer is that while I like many of the templating languages out there (my favorites being Twig, Smarty, Django, and Jinja2), I wanted something that combined the most useful capabilities of those languages in a more elegant and compact notation with a focus on the particular style of data binding that I want.  I have met this goal so far, but it will take some time for me to be sure that the language can be extended to all the uses that I will have for it.  Beyond that, I have no idea yet whether this language will be general-purpose enough to compete with the other options that are available.

The performance of my first revision is reasonable, considering that I wrote it in PHP.  I designed it to provide a good mix of flexibility and performance, so it beats most other templating engines, but not by much.  In lieu of a full feature list (since I am still adding functionality), I will just provide an example to demonstrate the syntax.  Here is a basic template with variables, in-line template definitions, includes, and binding blocks.

	{@TemplateBase}
	<!DOCTYPE html>
	<html>
	<head>
		<title>{$title}</title>
	</head>
	<body>
		<div id="header">
			{@Header}
				<h1><a href="{$root-url}">jacere.net</a></h1>
				{@Navigation}
					<div id="nav">
						<ul>
							{?nav-list}
								<li><a href="{$url}">{$text}</a></li>
							{/?}
						</ul>
					</div>
				{/@}
			{/@}
		</div>
	 
		<div id="content">
			{$content}
		</div>
		<div id="sidebar">
			{#Sidebar}
		</div>
	</body>
	</html>
	{/@}

Here is an example of inheriting this base template to show a list of posts.

	{@Posts}
		{^TemplateBase}
		{.content}
			{?list}
				{#PostSection}
			{/?}
		{/.}
	{/@}
	 
	{@PostSection}
	<div class="article">
		<div class="title">
			<small>Posted by: <strong>{$author}</strong> | {$date}</small>
			<h2><a href="{$url}">{$title}</a></h2>
		</div>
		<div class="post">
			<div class="entry">
				{$content}
			</div>
			<div class="postinfo">
				Posted in
				<ul class="taglist">
					{?categories}
						<li><a href="{$url}">{$name}</a></li>
					{/?}
				</ul>
			</div>
		</div>
	</div>
	{/@}

The block closing syntax that I am using is mostly for style reasons.  The following closing formats are parsed identically.

	{/}
	{/@}
	{/name}

Similarly, the parser accepts alternate delimiters, so the individual brace format can be replaced as desired (e.g. "{$var}" could be "<$var>"; or "{{$var}}").

As for using the template, right now I am simply binding to nested PHP arrays.  I have not yet decided where I want to go from here.

	$context = [
		'Posts' => [
			'title'	=> 'templ@te',
			'list'	 => [
				[
					'author'  => 'Joshua Morey',
					'date'	=> '2013-03-14',
					'url'	 => '/2013/03/satr-development-finalized/',
					'title'   => 'SATR Development Finalized',
					'content' => '<p>I finally found some time...</p>',
					'categories' => [
						['url' => '/category/c/', 'name' => 'C#'],
						['url' => '/category/cloudae/', 'name' => 'CloudAE'],
						['url' => '/category/lidar/', 'name' => 'LiDAR'],
					],
				],
				[
					'author'  => 'Joshua Morey',
					'date'	=> '2011-12-23',
					'url'	 => '/2011/12/tile-stitching/',
					'title'   => 'Tile Stitching',
					'content' => '<p>I have completed...</p>',
					'categories' => [
						['url' => '/category/cloudae/', 'name' => 'CloudAE'],
					],
				],
			]
		],
		'Header' => [
			'root-url' => '/template/parse.php',
		],
		'Navigation' => [
			'nav-list' => [
				['url' => '/about', 'text' => 'Contact'],
				['url' => '/contact', 'text' => 'About'],
			],
		],
	];
	 
	$output = TemplateManager::Evaluate('Posts', $context);

There are many things I plan to add/improve, such as filters (e.g. escaping), compacted syntax combinations, more versatile named bindings, and improved caching performance.  Eventually, I will also choose a name for the project, since "templ@te" isn't very web-friendly.

[twig]: http://twig.sensiolabs.org/  "Twig"
[smarty]: http://www.smarty.net/  "Smarty"
[jinja2]: http://jinja.pocoo.org/  "Jinja2"
[cheetah]: http://www.cheetahtemplate.org/  "Cheetah"
[genshi]: http://genshi.edgewall.org/  "Genshi"
[django]: https://www.djangoproject.com/  "Django"
[mako]: http://www.makotemplates.org/  "Mako"
[myghty]: http://www.myghty.org/  "Myghty"
[ctemplate]: http://code.google.com/p/ctemplate/  "ctemplate"


I finally found some time to finish my SATR tiling algorithm for [CloudAE][].  Unlike the previous STAR algorithms, which were built around segmented tiling, this approach uses more efficient spatial indexing to eliminate temporary files and to minimize both read and write operations.  Another difference with this approach is that increasing the allowed memory usage for the tiling process will substantially improve the performance by lowering the duplicate read multiplier and taking advantage of more sequential reads.  For instance, increasing the indexing segment size from 256 MB to 1 GB will generally reduce the tiling time by 50% on magnetic media, and 30% on an SSD.


[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"


[![](http://blog.jacere.net/wp-content/uploads/2011/12/Nx270xtoronto_segment-270x300.png.pagespeed.ic.O4QbFDGm9m.png)](http://blog.jacere.net/wp-content/uploads/2011/12/toronto_segment.png)
Since the framework performance has reached the goals that I set for myself, I have paused work on tiling optimizations and moved on to algorithm development.  The first step was to build a raster format data source.  I decided to go with a non-interleaved tiled binary format, which fits well with my intended usage.  In the future, I will make a more robust determination of my needs with regard to raster support.

One of the first high-performance algorithms that I developed in years past was a basic region-grow.  I have now implemented my tile-based segmentation algorithm (region-growing) for 2D analysis.  This allows me to perform segmentation on an interpolated cloud or raster, using the tiles to allow a low memory footprint.

Soon, I will need to make a vector output source, possibly SHP.  That will give me better geometry to visualize than the textured meshes I am currently using in the 3D viewer.


[![](http://blog.jacere.net/wp-content/uploads/2012/02/cloud_terrestrial_test1-300x217.png.pagespeed.ce.yJzLGm_EhG.png "cloud_terrestrial_test1")](http://blog.jacere.net/wp-content/uploads/2012/02/cloud_terrestrial_test1.png)
I have implemented a simple 3D point cloud visualization control intended to test whether WPF 3D can be reasonably used as a point cloud viewer.  Up to this point, I have been rendering clouds using [TIN][] geometry because WPF only supports meshes.

Using indexed meshes, I generated geometry that could represent points in 3D space.  At first, I tried to make the points visible from all angles, but that required too much memory from the 3D viewer even for simple clouds.  I reduced the memory footprint by only generating geometry for the top face of the pseudo-points, which allowed small clouds to be rendered.  For large clouds, I had to thin down to fewer than 1m points before generating geometry.  Even at this point, I was forced to make the point geometry larger than desired in order for it to not disappear when rendered.

The conclusion from this test is that WPF 3D is totally unsuitable for any type of large-scale 3D point rendering.  Eventually, I will need to move to [OpenGL][], [Direct3D][], or some API such as [OSG][] or [XNA][].


[tin]: http://en.wikipedia.org/wiki/Triangulated_irregular_network "Triangulated Irregular Network"
[opengl]: http://en.wikipedia.org/wiki/OpenGL "OpenGL"
[direct3d]: http://en.wikipedia.org/wiki/Microsoft_Direct3D "Direct3D"
[openscenegraph]: http://en.wikipedia.org/wiki/OpenSceneGraph "OpenSceneGraph"
[xna]: http://en.wikipedia.org/wiki/Microsoft_XNA "Microsoft XNA"


Years ago, in the days when I developed my PHP CMS, I decided to start a new project to give me more practice with new PHP language features.  In the end, I decided to build a parser for malformed HTML.

Lets all just take a moment to let that sink in...I built an HTML parser in PHP, in spite of the availability of modules such as libxml.  Now, it's not quite as bad as it sounds.  I did first look into the current options for markup parsing and recognized that my parser was not going to improve on any of them.  I was really just doing it for fun.

So, I jumped right in.  HTML parsing is fairly straightforward, and in the end I had a robust DOM parser that handled all manner of different malformed conditions.  Once that was complete, I continued adding functionality until eventually I had a full validating parser.  Throughout this development, I didn't spend much time thinking about performance.  So, as much as I liked it, it would never have been suitable for a production environment.

Recently, I ran across the old source code for that application and decided to port it to .NET, just out of curiosity.  I knew that I had not considered performance much during that phase of my programming career, and I wondered just how slow it was.  After converting it to C#, I benchmarked it against some current parsers, and discovered that it was abysmal.

So now it was time for a new project.  I looked into modern DOM, SAX, and Pull parsers and did not find any .NET implementations that could rival the speed of parsers such as [pugixml][], [AsmXml][], and [RapidXml][] (although in the latter case some of that speed comes from being a bit loose with the spec).  Having done a lot of .NET benchmarking for my [CloudAE][] project, I was curious how fast of a conforming HTML/XML parser I could write in C#.

Welcome the [Snail XML Parser][snail].


[pugixml]: http://pugixml.org/ "pugixml"
[asmxml]: http://tibleiz.net/asm-xml/ "AsmXml"
[rapidxml]: http://rapidxml.sourceforge.net/ "RapidXml"

[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"
[snail]: http://blog.jacere.net/snail/  "Snail XML Parser"


After extensive testing, I have optimized the text to binary conversion for ASCII XYZ files.  These files are a simple delimited format with XYZ values along with any number of additional attributes. My original naive approach used [StreamReader.ReadLine()][readline] and [Double.TryParse()][tryparse].  I quickly discovered that when converting billions of points from XYZ to binary, the parse time was far slower than the output time, making it the key bottleneck for the process.

Although the TryParse methods are convenient for normal use, they are far too slow for my purposes, since the points are in a simple floating point representation.  I implemented a reader to optimize the parse for that case, ignoring culture rules, scientific notation, and many other cases that are normally handled within the TryParse.  In addition, the parse performance of [atof()][atof]-style operations varies considerably between implementations.  The best performance I could come up with was a simple variation of a common idea with the addition of a lookup table.  The main cost of the parse is still the conditional branches.

In the end, I used custom parsing to identify lines directly in bytes without the overhead of memory allocation/copying and converting to unicode strings.  From there, I parse out the digits using the method I described.  I also made a variation that used only incremented pointers instead of array indices, but in the current .NET version, the performance was practically identical, so I reverted to the index version for ease of debugging.

The following test code provides reasonable performance for parsing three double-precision values per line.

	bool ParseXYZ(byte* p, int start, int end, double* xyz)
	{
		for (int i = 0; i < 3; i++)
		{
			long digits = 0;
	 
			// find start
			while (start < end && (p[start] < '0' || p[start] > '9'))
				++start;
	 
			// accumulate digits (before decimal separator)
			int currentstart = start;
			while (start < end && (p[start] >= '0' && p[start] <= '9'))
			{
				digits = 10 * digits + (p[start] - '0');
				++start;
			}
	 
			// check for decimal separator
			if (start > currentstart && start < end && p[start] == '.')
			{
				int decimalPos = start;
				++start;
	 
				// accumulate digits (after decimal separator)
				while (start < end && (p[start] >= '0' && p[start] <= '9'))
				{
					digits = 10 * digits + (p[start] - '0');
					++start;
				}
	 
				xyz[i] = digits * c_reciprocal[start - decimalPos - 1];
			}
			else
				xyz[i] = digits;
	 
			if (start == currentstart || digits < 0)
				return false; // no digits or too many (overflow)
		}
	 
		return true;
	}

[readline]: http://msdn.microsoft.com/en-us/library/system.io.streamreader.readline.aspx "StreamReader.ReadLine Method"
[tryparse]: http://msdn.microsoft.com/en-us/library/system.double.tryparse.aspx "Double.TryParse Method"
[atof]: http://www.cplusplus.com/reference/clibrary/cstdlib/atof/ "atof"


[![alt][78m_stitching1-img]](http://blog.jacere.net/wp-content/uploads/2012/02/78m_stitching1.png  "78m_stitching1")
I have completed a basic tile stitching algorithm for use in completing the 3D mesh view.  At this point, it only stitches equal resolutions, so it will stitch low-res tiles together and high-res tiles together, but it will leave a visible seam between tiles of different resolutions.  Obviously, that is not a difficult problem, since I am only stitching regular meshes at this point.  However, I do plan to get irregular mesh support eventually.  Currently, I have an incremental Delaunay implementation, but the C# version is much slower than the original C++, to the extent that it is not worth running.  The [S-hull][] implementation that I downloaded is much faster, but does not produce correct results.

The primary limitation of the tile-based meshing and stitching is that WPF 3D has performance limitations regarding the number of [MeshGeometry3D][] instances in the scene.  For small to medium-sized files, there may be up to a few thousand tiles with the current tile-sizing algorithms.  WPF 3D performance degrades substantially when the number of mesh instances gets to be approximately 12,000.  Massive files may have far more tiles than that, and adding mesh instances for the stitching components makes the problem even worse.  I have some ideas for workarounds, but I have not yet decided if they are worth implementing since there are already so many limitations with WPF 3D and this 3D preview was never intended to be a real viewer.

![alt][78m_stitching3-img]


[s-hull]: http://www.s-hull.org/  "S-hull"
[meshgeometry3d]: http://msdn.microsoft.com/en-us/library/system.windows.media.media3d.meshgeometry3d.aspx  "MeshGeometry3D Class"

[78m_stitching1]: http://blog.jacere.net/wp-content/uploads/2012/02/78m_stitching1.png  "78m_stitching1"

[78m_stitching1-img]: http://blog.jacere.net/wp-content/uploads/2012/02/78m_stitching1-300x217.png.pagespeed.ce.4wFFGpWx_k.png  "78m_stitching1"
[78m_stitching3-img]: http://blog.jacere.net/wp-content/uploads/2012/02/500x350x78m_stitching3.png.pagespeed.ic.OuzYQPtTWC.png  "78m_stitching3"

Compiling [LASzip][] is simple, but what what does performance look like when using LASzip in a managed environment?  The first thing to realize is that accessing points individually is very expensive across a managed boundary.  That means that using an equivalent of P/Invoke individually for each point will add a substantial amount of overhead in a C# context.  To reduce the number of [interop thunks][interop] which need to occur, the most important step is to write an intermediate class in native C++ which can retrieve the individual points and return them in blocks, transforming calls from this:

	bool LASunzipper::read(unsigned char * const * point);

...to something like this:

	int LAZBlockReader::Read(unsigned char* buffer, int offset, int count);

The next, less important, performance consideration is to create a C++/CLI interop layer to interface with the block reader/writer.  This allows us to hide details like marshaling and pinning, and uses the C++ Interop, which provides optimal performance compared to P/Invoke.

For my situation, this is exactly what I want, since [CloudAE][] is built around chunk processing anyway.  For other situations, both the "block" transformation and the interop layer can be an annoying sort of overhead, so it should definitely be benchmarked to determine whether the thunk reduction cost is worth it.

The final factor determining the performance of LASzip is the file I/O.  In [LAStools][], Martin Isenburg uses a default `io_buffer_size` parameter that is currently 64KB.  Using a similarly appropriate buffer size is the easiest way to get reasonable performance.  Choosing an ideal buffer size is a complex topic that has no single answer, but anything from 64KB to 1MB is generally acceptable.  For those not familiar with the LASzip API, `LASunzipper` can use either a `FILE` handle or an `iostream` instance, and either of these types can use a custom buffer size.

One caveat that I mentioned in my [last post][laz-support] is that when compiling a C++/CLI project in VS 2010, the behavior of customizing iostream buffer sizes is buggy.  As a result, I ended up using a `FILE` handle and [setvbuf()][setvbuf].  The downside of this approach is that LAZ support in my application cannot currently use all my optimized I/O options, such as using [FILE\_FLAG\_NO\_BUFFERING][buffering] when appropriate.

For an example of using the LASzip API from C++, check out the [libLAS source][liblas].


[laszip]: http://www.laszip.org/  "LASZip"
[lastools]: http://www.cs.unc.edu/~isenburg/lastools/  "LAStools"
[liblas]: http://www.liblas.org/ "libLAS"
[interop]: http://msdn.microsoft.com/en-us/library/ky8kkddw.aspx  "Performance Considerations for Interop (C++)"
[setvbuf]: http://www.cplusplus.com/reference/clibrary/cstdio/setvbuf/  "setvbuf"
[buffering]: http://msdn.microsoft.com/en-us/library/windows/desktop/cc644950.aspx  "File Buffering"

[cloudae]: http://blog.jacere.net/cloudae/  "CloudAE"
[laz-support]: http://blog.jacere.net/2012/09/laz-support/  "LAZ Support"
