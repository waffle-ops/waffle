<?php

namespace Waffle\Command;

interface DiscoverableCommandInterface
{
    // This interface is used to pull commands into the DI container. Instead
    // of relying on the base class, we will lean on this interface so that
    // we can intentonally skip commands that should not be loaded (ie. Task,
    // Recipe, etc...).
}
