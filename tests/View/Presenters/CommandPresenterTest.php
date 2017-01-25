<?php

namespace REBELinBLUE\Deployer\Tests\View\Presenters;

use Lang;
use Mockery as m;
use REBELinBLUE\Deployer\Command;
use REBELinBLUE\Deployer\Project;
use REBELinBLUE\Deployer\Tests\TestCase;
use REBELinBLUE\Deployer\View\Presenters\CommandPresenter;

class CommandPresenterTest extends TestCase
{
    /**
     * @dataProvider getMethods
     */
    public function testPresentMethodsReturnTranslation($method)
    {
        $expected = 'app.none';

        $project = m::mock(Project::class);
        $project->shouldReceive('getAttribute')->with('commands')->andReturn([]);

        Lang::shouldReceive('get')->with($expected)->andReturn($expected);

        $presenter = new CommandPresenter($project);
        $actual    = $presenter->{$method}();

        $this->assertEquals($expected, $actual, $method . ' did not translate');
    }

    /**
     * @dataProvider getCommandsAndMethods
     */
    public function testPresentMethodsReturnCommandNames($method, $expected, $commands)
    {
        $collection = [];

        foreach ($commands as $command) {
            $collection[] = $this->mockCommand($command[0], $command[1]);
        }
        $commands = collect($collection);

        $project = m::mock(Project::class);
        $project->shouldReceive('getAttribute')->with('commands')->andReturn($commands);

        $presenter = new CommandPresenter($project);
        $actual    = $presenter->{$method}();

        $this->assertEquals($expected, $actual, $method . ' did not return expected names');
    }

    private function mockCommand($name, $step)
    {
        $command = m::mock(Command::class);
        $command->shouldReceive('getAttribute')->with('step')->andReturn($step);
        $command->shouldReceive('getAttribute')->with('name')->andReturn($name);

        return $command;
    }

    public function getMethods()
    {
        return array_chunk([
            'presentBeforeClone', 'presentAfterClone', 'presentBeforeInstall', 'presentAfterInstall',
            'presentBeforeActivate', 'presentAfterActivate', 'presentBeforePurge', 'presentAfterPurge',
        ], 1);
    }

    public function getCommandsAndMethods()
    {
        $data = [];
        foreach ($this->getSteps() as $step) {
            $method   = $step[0];
            $expected = $step[1];
            $other    = $step[2];

            $data[] = [$method, 'step1', [['step1', $expected]]];
            $data[] = [$method, 'step1', [['step1', $expected], ['step2', $other]]];
            $data[] = [$method, 'step1, step2', [
                ['step1', $expected], ['step2', $expected],
            ]];
            $data[] = [$method, 'step1, step3', [
                ['step1', $expected], ['step2', $other], ['step3', $expected],
            ]];
            $data[] = [$method, 'step1, step2, step3', [
                ['step1', $expected], ['step2', $expected], ['step3', $expected],
            ]];
        }

        return $data;
    }

    private function getSteps()
    {
        return [
            ['presentBeforeClone',    Command::BEFORE_CLONE,    Command::AFTER_CLONE],
            ['presentAfterClone',     Command::AFTER_CLONE,     Command::AFTER_PURGE],
            ['presentBeforeInstall',  Command::BEFORE_INSTALL,  Command::BEFORE_ACTIVATE],
            ['presentAfterInstall',   Command::AFTER_INSTALL,   Command::AFTER_PURGE],
            ['presentBeforeActivate', Command::BEFORE_ACTIVATE, Command::BEFORE_CLONE],
            ['presentAfterActivate',  Command::AFTER_ACTIVATE,  Command::BEFORE_PURGE],
            ['presentBeforePurge',    Command::BEFORE_PURGE,    Command::AFTER_INSTALL],
            ['presentAfterPurge',     Command::AFTER_PURGE,     Command::AFTER_ACTIVATE],
        ];
    }
}
