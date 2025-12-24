import { exec } from 'child_process';
import { promises as fs } from 'fs';
import minimatch from 'minimatch';
import osPath from 'path';
import { PluginContext } from 'rollup';
import { promisify } from 'util';
import { HmrContext, Plugin } from 'vite';

const execAsync = promisify(exec);

interface LaravelDataTypesOptions {
  patterns?: string[];
  command?: string;
  path?: string;
  extraArgs?: string[];
  /**
   * If true, automatically replaces verbose paginator types with
   * a generic LengthAwarePaginator<T> type.
   * Requires the 'path' option to be set.
   * @default true
   */
  refactorPaginators?: boolean;
}

let context: PluginContext;

/**
 * Refactors the verbose Laravel paginator type in the generated file.
 */
const refactorPaginatorInFile = async (
  pluginContext: PluginContext,
) => {
  const filePath = osPath.join('resources', 'js', 'types', 'generated.d.ts');

  try {
    const fullPath = osPath.resolve(filePath);
    const originalContent = await fs.readFile(fullPath, "utf-8");

    // Replace verbose paginator type
    const paginatorRegex =
      /\{data:Array<(.+?)>;links:Array<\{url:string \| null;label:string;active:boolean;\}>;meta:\{current_page:number;first_page_url:string;from:number \| null;last_page:number;last_page_url:string;next_page_url:string \| null;path:string;per_page:number;prev_page_url:string \| null;to:number \| null;total:number;\};\};/g;
    const replacementString = "LengthAwarePaginator<$1>;";
    let newContent = originalContent.replace(paginatorRegex, replacementString);

    // Remove all instances of "App.Data."
    newContent = newContent.replace(/App\.Data\./g, "");
    newContent = newContent.replace(/App\.Data/g, "Data");

    // Remove all instances of "App.Enums."
    newContent = newContent.replace(/App\.Enums/g, "Enums");

    if (originalContent !== newContent) {
      await fs.writeFile(fullPath, newContent, "utf-8");
      pluginContext.info(
        `[laravel-data] Refactored paginator types, removed "App.Data.", and replaced namespace declaration in ${filePath}.`,
      );
    }
  } catch (error) {
    if (error instanceof Error && "code" in error && error.code === "ENOENT") {
      pluginContext.warn(
        `[laravel-data] Could not find file to refactor at: ${filePath}`,
      );
    } else {
      pluginContext.error(
        `[laravel-data] Failed to refactor paginator types:\n${error}`,
      );
    }
  }
};


export const laravelDataTypes = ({
  patterns = ['app/Data/**/*.php', 'app/Enums/**/*.php'],
  command = 'php artisan typescript:transform',
  path,
  extraArgs = [],
  refactorPaginators = true,
}: LaravelDataTypesOptions = {}): Plugin => {
  patterns = patterns.map((pattern) => pattern.replace('\\', '/'));

  const args: string[] = [...extraArgs];
  if (path) {
    args.push(`--path=${path}`);
  }

  const runCommand = async () => {
    try {
      await execAsync(`${command} ${args.join(' ')}`);
      context.info('[laravel-data] TypeScript types generated.');

      if (refactorPaginators) {
        await refactorPaginatorInFile(context);
      }
    } catch (error) {
      context.error('[laravel-data] Failed to generate types:\n' + error);
    }
  };

  return {
    name: 'laravel-data-types',
    enforce: 'pre',
    buildStart() {
      // eslint-disable-next-line
      context = this;
      return runCommand();
    },
    async handleHotUpdate({ file, server }) {
      if (shouldRun(patterns, { file, server })) {
        await runCommand();
      }
    },
  };
};

const shouldRun = (patterns: string[], opts: Pick<HmrContext, 'file' | 'server'>): boolean => {
  const file = opts.file.replaceAll('\\', '/');

  return patterns.some((pattern) => {
    pattern = osPath.resolve(opts.server.config.root, pattern).replaceAll('\\', '/');
    return minimatch(file, pattern);
  });
};