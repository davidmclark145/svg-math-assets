# svg-math-assets
A family of classes created to dynamically generate SVG figures from math related input parameters

## Brief overview of software product
We make dynamic math questions that students practice to increase their scores on standardized state tests. They are dynamic because each core math problem/concept has potentially hundreds or thousands of unique variations rather than being just one, static, hand-written problem like is traditionally sold to school districts in workbooks.

Thus, we allow students to develop fluency and mastery over each concept by being able to solve as many unique variations of a math problem as they need to understand that underlying core concept.

## Goal of project
In our vast library of 'stems' (which are the core math concepts, described above, from which many variations 'stem'), we feature many forms of math problems that utilize visual elements such as diagrams, charts, graphs, shapes, etc.

Previously, these were made with raw HTML and CSS within the PHP files which made every stem that featured a visual element extremely bloated and difficult to maintain or comprehend.

Originally, the goal was to specifically target and update all the stems that featured clocks. If we could design a class that centralizes all of the often reused code, we could simply instantiate a Clock::class within each stem file. That way, we would only need to worry about the actual math problem's logic when maintaining it instead of having to also absorb and update tens of difficult-to-parse variables scattered across many large blocks of HTML tags.

## Outcome
I wrote all of this code myself, with some input and discussion from my team over time as I went. It took several months as I had to go back and forth between various other projects.

Initially, I started with a monolithic clock class that I thought would serve its purpose well enough as the scope was to only target clock-using stems.

However, it quickly became apparent what a tremendous opportunity we had with the way we could organize SVG and HTML code that we in fact duplicated across so many files and in so many contexts. Thus, the new goal became a game of reducing as many lines of duplicated code across as many visual type stems as possible.

This not only helped us refactor countless bloated stem files, but it also simplified such a tedious process that it enabled us to start creating much higher quality stems much more often, using more types of visual assets, and in fact being even more creative with them than we had ever been before. Not to mention that, compared to the raw HTML/CSS, we now had more dynamic, visually appealing, and better resolution SVGs.