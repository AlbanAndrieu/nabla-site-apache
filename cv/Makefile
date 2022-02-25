TEX = pdflatex
TEX_OPTS = -shell-escape -interaction=nonstopmode -file-line-error
#TEX_OPTS = -synctex=1 -interaction=nonstopmode -file-line-error
BIB = bibtex
OPEN = xdg-open
MAX_TRY = 5

### Main LaTeX file
MAIN = cv-aandrieu-XX-XX-2021.tex

### Files that are included (producing .aux file)
INCLUDED_FILES =
#INCLUDED_FILES = \
#	intro.tex

### Files that are included/input (not producing .aux file)
OTHER_FILES =

SOURCES = \
	$(MAIN) \
	$(INCLUDED_FILES) \
	$(OTHER_FILES)

### Files that are not  included/input but should be in the distribution
EXTRA_DIST = \
#	CHANGELOG.md \
	Makefile \
	alban-andrieu-19-12-2017.png

VERSION = $(shell git describe --always --dirty)
DIST = $(MAIN:.tex=-$(VERSION).tar.gz)

PDF = $(MAIN:.tex=.pdf)

DIRTY_FILES = \
	$(MAIN:.tex=.aux) \
	$(INCLUDED_FILES:.tex=.aux) \
	$(MAIN:.tex=.bbl) \
	$(INCLUDED_FILES:.tex=.bbl) \
	$(MAIN:.tex=.blg) \
	$(INCLUDED_FILES:.tex=.blg) \
	$(MAIN:.tex=.done) \
	$(MAIN:.tex=.lof) \
	$(MAIN:.tex=.lot) \
	$(MAIN:.tex=.out) \
	$(MAIN:.tex=.toc)

NEED_RUN_MSG_REGEX=\(Rerun to get citations correct\)
#NEED_RUN_MSG_REGEX:=$(NEED_RUN_MSG_REGEX)\|\(Label(s) may have changed, Rerun to get cross-reference right.\))
#NEED_RUN_MSG_REGEX:=$(NEED_RUN_MSG_REGEX)\|\(Package rerunfilecheck Warning: File .* has changed.\))

.PHONY: all pdf view clean clean-all
.SUFFIX: .tex .aux .bbl .pdf .done

all: pdf

pdf: $(PDF)

view: $(PDF)
	$(OPEN) $(PDF)

dist: $(DIST)

clean:
	rm -f *~ */*~ $(DIRTY_FILES) $(DIST)

clean-all: clean
	rm -f $(PDF)

#run_tex: $(MAIN)
#	$(TEX) $(TEX_OPTS) "$<" \
#	"$(<:.tex=.log); "

run_tex: $(MAIN)
	CPT=0; \
	while \
		[ $${CPT} -lt $(MAX_TRY) ] \
		&& $(TEX) $(TEX_OPTS) "$<" \
		&& grep -q "$(NEED_RUN_MSG_REGEX)" \
		"$(<:.tex=.log)"; \
	do CPT=$$((CPT+1)) ; done

#.aux.bbl:
#	$(BIB) "$<" || true # since chapterbib provokes error code return

$(PDF): $(MAIN:.tex=.done)

$(MAIN:.tex=.done): $(SOURCES) $(EXTRA_DIST)
	make run_tex
#	make $(INCLUDED_FILES:.tex=.bbl) $(MAIN:.tex=.bbl) $(MAIN:.tex=.ind) $(MAIN:.tex=.gls)
#	make run_tex
#	make $(INCLUDED_FILES:.tex=.bbl) $(MAIN:.tex=.bbl) $(MAIN:.tex=.ind) $(MAIN:.tex=.gls)
#	make run_tex
	touch "$@"

$(DIST): $(SOURCES) $(EXTRA_DIST)
	tar -czf "$@" $^
