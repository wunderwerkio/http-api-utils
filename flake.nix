{
  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs/nixos-24.11";
    utils.url = "github:wunderwerkio/nix-utils";
  };

  outputs = {
    self,
    nixpkgs,
    utils,
  }: utils.lib.systems.eachDefault (system:
    let
      pkgs = import nixpkgs {
        inherit system;
      };
      prepareEnv = ''
        echo ""
        echo "> Run PHPStan"
        echo "> \$ composer analyze"
        echo ">"
        echo "> Run PHPUnit"
        echo "> \$ composer test"
      '';
    in {
      devShells = rec {
        # PHP 8.1
        php81 = pkgs.mkShell {
          buildInputs = [
            pkgs.php81
            pkgs.php81Packages.composer
          ];

          shellHook = prepareEnv;
        };

        # PHP 8.2
        php82 = pkgs.mkShell {
          buildInputs = [
            pkgs.php82
            pkgs.php82Packages.composer
          ];

          shellHook = prepareEnv;
        };

        # PHP 8.3
        php83 = pkgs.mkShell {
          buildInputs = [
            pkgs.php83
            pkgs.php83Packages.composer
          ];

          shellHook = prepareEnv;
        };

        # PHP 8.4
        php84 = pkgs.mkShell {
          buildInputs = [
            pkgs.php84
            pkgs.php84Packages.composer
          ];

          shellHook = prepareEnv;
        };

        default = php83;
      };

      formatter = pkgs.alejandra;
    }
  );
}

